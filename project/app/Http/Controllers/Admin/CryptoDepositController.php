<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CryptoDeposit;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Datatables;

class CryptoDepositController extends Controller
{
    public function datatables()
    {
        $datas = CryptoDeposit::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(CryptoDeposit $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(CryptoDeposit $data){
                            $data = User::where('id',$data->user_id)->first();
                            return str_dis($data->name);
                        })
                        ->editColumn('amount', function(CryptoDeposit $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('address', function(CryptoDeposit $data) {
                            return str_dis($data->address);
                        })
                        ->editColumn('sender_address', function(CryptoDeposit $data) {
                            return str_dis($data->sender_address);
                        })
                        ->editColumn('hash', function(CryptoDeposit $data) {
                            return str_dis($data->hash);
                        })
                        ->editColumn('status', function(CryptoDeposit $data) {
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
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.deposits.crypto.status', ['id1' => $data->id, 'id2' => 1]) . '">' . __("completed") . '</a>
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.deposits.crypto.status', ['id1' => $data->id, 'id2' => 2]) . '">' . __("rejected") . '</a>
                                                      </div>
                                                    </div>';
                            })
                        ->editColumn('action', function(CryptoDeposit $data) {
                            $doc_url = $data->proof ? $data->proof : null;
                            return '<input type="hidden", id="sub_data", value ='.json_encode($data).'>'.' <a href="javascript:;" data=\''.json_encode($data).'\' url="'.$doc_url.'" onclick="getDetails(this)" class="detailsBtn" >
                            ' . __("Details") . '</a>';
                        })
                        ->rawColumns(['created_at','customer_name','amount','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.cryptodeposit.index');
    }

    public function status($id1,$id2){
        $data = CryptoDeposit::findOrFail($id1);

        if($data->status == 1){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        if($data->status == 2){
            $msg = 'Deposits already rejected';
            return response()->json($msg);
          }

        $user = User::findOrFail($data->user_id);
        $amount = $data->amount/$data->currency->rate;
        $transaction_global_cost = 0;
        $transaction_global_fee = check_global_transaction_fee($amount, $user, 'deposit');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        $toWallet = Wallet::where('user_id', $user->id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();
        $fromWallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $data->currency_id)->first();
        $currency = Currency::findOrFail($data->currency_id);
        if ($id2 == 1) {
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'deposit');

                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'Deposit_create_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 6)->where('currency_id', $data->currency_id)->first();
                    user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$data->currency->rate, 6);

                    $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 10)->where('currency_id', $data->currency_id)->first();
                    user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$data->currency->rate, 10);
                    $remark = 'Deposit_create_manager_fee';

                    $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 10);
                }
                if($currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword, 30]);
                    $tx = '{from: "'.$fromWallet->wallet_no.'", to: "'.$torefWallet->wallet_no.'", value: web3.toWei('.$transaction_custom_cost*$data->currency->rate.', "ether")}';
                    RPC_ETH('personal_sendTransaction',[$tx, $fromWallet->keyword]);
                }
                elseif($currency->code == 'BTC') {
                    RPC_BTC_Send('sendtoaddress',[$torefWallet->wallet_no, $transaction_custom_cost*$data->currency->rate],$fromWallet->keyword);
                }
                $referral_user = User::findOrFail($user->referral_id);
                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $data->currency_id;
                $trans->amount      = $transaction_custom_cost*$data->currency->rate;
                $trans->charge      = 0;
                
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Deposit complete');
                $trans->data        = '{"sender":"System Account", "receiver":"'.$referral_user->name.'"}';
                $trans->save();
            }

            $final_amount = $amount - $transaction_custom_cost - $transaction_global_cost;

            $result1 = user_wallet_increment($user->id, $data->currency_id, $final_amount*$data->currency->rate, 8);
            $result2 = user_wallet_increment(0, $data->currency_id, $transaction_global_cost*$data->currency->rate, 9);

            if($currency->code == 'ETH') {
                RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword, 30]);
                $tx = '{from: "'.$fromWallet->wallet_no.'", to: "'.$toWallet->wallet_no.'", value: web3.toWei('.$final_amount*$data->currency->rate.', "ether")}';
                RPC_ETH('personal_sendTransaction',[$tx, $fromWallet->keyword]);
            }
            elseif($currency->code == 'BTC') {
                RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, $final_amount*$data->currency->rate],$fromWallet->keyword);
            }
            if(!$result1 || !$result2) {
              return response()->json(array('errors' => [ 0 =>  __('Crypto node is not installed.') ]));
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $data->amount;
            $trans->charge      = ($transaction_custom_cost + $transaction_global_cost)*$data->currency->rate;
            $trans_wallet       = get_wallet($user->id, $data->currency_id, 8);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->type        = '+';
            $trans->remark      = 'Deposit_create';
            $trans->details     = trans('Deposit complete');
            $trans->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
            $trans->save();
        }


        $data->status = $id2;
        $data->update();
        $gs = Generalsetting::findOrFail(1);

            $to = $user->email;
            $subject = " You have deposited successfully.";
            $msg = "Hello ".$user->name."!\nYou have deposited successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}

