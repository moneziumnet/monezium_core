<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\User;
use App\Models\Wallet;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\CryptoDeposit;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
                            return str_dis($data->company_name ?? $data->name);
                        })
                        ->editColumn('amount', function(CryptoDeposit $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('address', function(CryptoDeposit $data) {
                            return str_dis($data->address);
                        })
                        ->editColumn('action', function(CryptoDeposit $data) {
                            $doc_url = $data->proof ? $data->proof : null;
                            return '<input type="hidden", id="sub_data", value ='.json_encode($data).'>'.' <a href="javascript:;" data=\''.json_encode($data).'\' url="'.$doc_url.'" onclick="getDetails(this)" class="detailsBtn" >
                            ' . __("Details") . '</a>';
                        })
                        ->rawColumns(['created_at','customer_name','amount','action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.cryptodeposit.index');
    }

    public function status($id1,$id2){
        $data = CryptoDeposit::findOrFail($id1);
        $gs = Generalsetting::first();

        if($data->status == 1){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        if($data->status == 2){
            $msg = 'Deposits already rejected';
            return response()->json($msg);
          }

        $user = User::findOrFail($data->user_id);

        $currency = Currency::where('id',$data->currency_id)->first();
        $crypto_rate = getRate($currency);
        $amount = $data->amount/$crypto_rate;
        $transaction_global_cost = 0;
        $transaction_global_fee = check_global_transaction_fee($amount, $user, 'deposit_crypto');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        $toWallet = Wallet::where('user_id', $user->id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();
        $fromWallet = get_wallet(0,$data->currency_id,9);
        $currency = Currency::findOrFail($data->currency_id);
        if ($id2 == 1) {
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'deposit_crypto');

                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'Deposit_create_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$crypto_rate, 8);
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();

                    $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 8);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$crypto_rate, 8);
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $data->currency_id)->first();
                    $remark = 'Deposit_create_manager_fee';

                    $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 8);
                }
                if($currency->code == 'ETH') {
                    @RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$torefWallet->wallet_no.'", "value": "0x'.dechex($transaction_custom_cost*$crypto_rate*pow(10,18)).'"}';
                    @RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
                }
                elseif($currency->code == 'BTC') {
                    $res = RPC_BTC_Send('sendtoaddress',[$torefWallet->wallet_no, amount($transaction_custom_cost*$crypto_rate, 2)],$fromWallet->keyword);
                    if (isset($res->error->message)){
                        return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                    }
                }
                $referral_user = User::findOrFail($user->referral_id);
                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $data->currency_id;
                $trans->amount      = $transaction_custom_cost*$crypto_rate;
                $trans->charge      = 0;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Deposit complete');
                $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($referral_user->company_name ?? $referral_user->name).'"}';
                $trans->save();
            }

            $final_amount = $amount - $transaction_custom_cost - $transaction_global_cost;

            $result1 = user_wallet_increment($user->id, $data->currency_id, $final_amount*$crypto_rate, 8);
            $result2 = user_wallet_increment(0, $data->currency_id, $transaction_global_cost*$crypto_rate, 9);

            if($currency->code == 'ETH') {
                RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$toWallet->wallet_no.'", "value": "0x'.dechex($final_amount*$crypto_rate*pow(10,18)).'"}';
                $res = RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
            }
            elseif($currency->code == 'BTC') {
                $res = RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, amount($final_amount*$crypto_rate, 2)],$fromWallet->keyword);
                if (isset($res->error->message)){
                    return response()->json(array('errors' => [ 0 =>  __('you can not deposit because ') . $res->error->message]));
                }
            }
            if($result1 == null || $result2 == null) {
              return response()->json(array('errors' => [ 0 =>  __('Crypto node is not installed.') ]));
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $data->amount;
            $trans->charge      = ($transaction_custom_cost + $transaction_global_cost)*$crypto_rate;
            $trans_wallet       = get_wallet($user->id, $data->currency_id, 8);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->type        = '+';
            $trans->remark      = 'Deposit_create';
            $trans->details     = trans('Deposit complete');
            $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $trans->save();
        }


        $data->status = $id2;
        $data->update();
            $to = $user->email;
            $subject = " You have deposited successfully.";
            $msg = "Hello ".$user->name."!\nYou have deposited successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            sendMail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}

