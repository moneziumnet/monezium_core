<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\User;
use App\Models\wallet;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\CryptoWithdraw;
use App\Models\Generalsetting;
use App\Classes\EthereumRpcService;
use App\Http\Controllers\Controller;

class CryptoWithdrawController extends Controller
{
    public function datatables()
    {
        $datas = CryptoWithdraw::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(CryptoWithdraw $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(CryptoWithdraw $data){
                            $data = User::where('id',$data->user_id)->first();
                            return str_dis($data->name);
                        })
                        ->editColumn('hash',function(CryptoWithdraw $data){
                            return str_dis($data->hash);
                        })
                        ->editColumn('sender_address',function(CryptoWithdraw $data){
                            return str_dis($data->sender_address);
                        })
                        ->editColumn('amount', function(CryptoWithdraw $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('status', function(CryptoWithdraw $data) {
                            if ($data->status == 1) {
                                $status  = __('Completed');
                              } elseif ($data->status == 2) {
                                $status  = __('Rejected');
                              } else {
                                $status  = __('Pending');
                              }

                              if ($data->status == 1) {
                                $status_sign  = 'success';
                              } elseif ($data->status == 2) {
                                $status_sign  = 'danger';
                              } else {
                                $status_sign = 'warning';
                              }

                              return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ' . $status . '
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.withdraws.crypto.status', ['id1' => $data->id, 'id2' => 1]) . '">' . __("completed") . '</a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.withdraws.crypto.status', ['id1' => $data->id, 'id2' => 2]) . '">' . __("rejected") . '</a>
                                    </div>
                                </div>';
                            })
                        ->editColumn('action', function(CryptoWithdraw $data) {
                            return '<input type="hidden", id="sub_data", value ='.json_encode($data).'>'.' <a href="javascript:;"   onclick=getDetails('.json_encode($data).') class="detailsBtn" >
                            ' . __("Details") . '</a>';
                        })
                        ->rawColumns(['created_at','customer_name','amount','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.cryptowithdraw.index');
    }

    public function status($id1,$id2){
        $data = CryptoWithdraw::findOrFail($id1);

        if($data->status == 1){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        if($data->status == 2){
            $msg = 'Deposits already rejected';
            return response()->json($msg);
          }

        $user = User::findOrFail($data->user_id);

        $data->status = $id2;
        $data->update();
        $currency = Currency::findOrFail($data->currency_id);

        $client = New Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=USD');
        $rate = json_decode($response->getBody());
        $code = $currency->code;
        $crypto_rate = $rate->data->rates->$code ?? $currency->rate;

        if ($id2 == 2) {
            $user = $data->user;
            //$wallet = Wallet::where('user_id',$data->user_id)->where('user_type',1)->where('currency_id',$data->currency_id)->firstOrFail();
            $toWallet = Wallet::where('user_id', $user->id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();
            $fromWallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $data->currency_id)->first();

            user_wallet_increment($data->user_id, $data->currency_id, $data->amount, 8);
            $transaction_global_cost = 0;

            $transaction_global_fee = check_global_transaction_fee($data->amount/$crypto_rate, $user, 'withdraw');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($data->amount/($crypto_rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            user_wallet_decrement(0, $data->currency_id, $transaction_global_cost * $crypto_rate, 9);

            if($user->referral_id != 0)
            {
                $transaction_custom_cost = 0;
                $transaction_custom_fee = check_custom_transaction_fee($data->amount/$crypto_rate, User::whereId($data->user_id)->first(), 'withdraw');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($data->amount/(100*$crypto_rate)) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'withdraw_reject_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_decrement($user->referral_id, $data->currency_id, $transaction_custom_cost*$crypto_rate, 8);

                    $trans_wallet  = get_wallet($user->referral_id, $data->currency_id, 8);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'withdraw_reject_manager_fee';
                    user_wallet_decrement($user->referral_id, $data->currency_id, $transaction_custom_cost*$crypto_rate, 8);

                    $trans_wallet  = get_wallet($user->referral_id, $data->currency_id, 8);
                }


                if($currency->code == 'ETH') {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();

                    RPC_ETH('personal_unlockAccount',[$torefWallet->wallet_no, $torefWallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$torefWallet->wallet_no.'", "to": "'.$toWallet->wallet_no.'", "value": "0x'.dechex($transaction_custom_cost*$crypto_rate*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $torefWallet->keyword ?? '');
                }
                elseif($currency->code == 'BTC') {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();
                    RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, $transaction_custom_cost*$crypto_rate],$torefWallet->keyword);
                }
                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $data->currency_id;
                $trans->amount      = $transaction_custom_cost*$crypto_rate;
                $trans->charge      = 0;
                $trans->type        = '-';

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->remark      = $remark;
                $trans->details     = trans('Withdraw request rejected');
                $trans->data        = '{"sender":"'.User::findOrFail($user->referral_id)->name.'", "receiver":"'.$user->name.'"}';
                $trans->save();
            }
            if($currency->code == 'ETH') {
                RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$toWallet->wallet_no.'", "value": "0x'.dechex(($data->amount-$transaction_custom_cost*$crypto_rate)*pow(10,18)).'"}';
                RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
            }
            elseif($currency->code == 'BTC') {
                RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, ($data->amount-$transaction_custom_cost*$crypto_rate)],$fromWallet->keyword);
            }
            else {
                RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                $geth = new EthereumRpcService();
                $tokenContract = $fromWallet->currency->address;
                $geth->transferToken($tokenContract, $fromWallet->wallet_no, $toWallet->wallet_no, $data->amount-$transaction_custom_cost*$crypto_rate);
            }
            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $data->user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $data->currency->id;
            $trnx->amount      = $data->amount;

            $trans_wallet      = get_wallet($data->user_id, $data->currency_id, 8);
            $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trnx->charge      = 0;
            $trnx->remark      = 'withdraw_reject';
            $trnx->type        = '+';
            $trnx->details     = trans('Withdraw request rejected');
            $trnx->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
            $trnx->save();


        }
        $gs = Generalsetting::findOrFail(1);

            $to = $user->email;
            $subject = " You have withdrawed successfully.";
            $msg = "Hello ".$user->name."!\nYou have withdrawed successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }

    public function edit($id) {
        $data['withdraw'] = CryptoWithdraw::findOrFail($id);
        return view('admin.cryptowithdraw.edit', $data);
    }

    public function update(Request $request, $id) {
        $data = CryptoWithdraw::findOrFail($id);
        $data->hash = $request->hash;
        $data->update();
        return response()->json('You have added hash value successfully. '.'<a href="'.route('admin.withdraws.crypto.index').'"> '.__('View Lists.').'</a>');

    }
}

