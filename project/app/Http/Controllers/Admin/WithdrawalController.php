<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Models\Withdrawals;
use App\Models\Currency;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Charge;
use DB;

class WithdrawalController extends Controller
{
    public function accepted()
    {
        $withdrawlogs = Withdrawals::where('status', 1)->latest()->with(['method','user','currency'])->paginate(15);
        return view('admin.withdraw.withdraw_all',compact('withdrawlogs'));
    }
    public function pending()
    {
        $withdrawlogs = Withdrawals::where('status', 0)->latest()->with(['method','user','currency'])->paginate(15);
        return view('admin.withdraw.withdraw_all',compact('withdrawlogs'));
    }
    public function rejected()
    {
        $withdrawlogs = Withdrawals::where('status', 2)->latest()->with(['method','user','currency'])->paginate(15);
        return view('admin.withdraw.withdraw_all',compact('withdrawlogs'));
    }

    public function withdrawAccept(Withdrawals $withdraw)
    {
        $withdraw->status = 1;
        $withdraw->save();


      mailSend('accept_withdraw',['amount'=>amount($withdraw->amount,$withdraw->currency->type,2), 'trnx'=> $withdraw->trx,'curr' => $withdraw->currency->code,'method'=>$withdraw->method->name,'charge'=> amount($withdraw->charge,$withdraw->currency->type,2),'date_time'=> dateFormat($withdraw->updated_at)], $withdraw->user);
      send_notification($withdraw->user->id, $withdraw->method->name.' Withdraw  for '.($withdraw->user->company_name ?? $withdraw->user->name).' is approved.'."\n Charge Fee : ".amount($withdraw->charge,$withdraw->currency->type,2).$withdraw->currency->code."\n Transaction ID : ".$withdraw->trx, route('admin.withdraw.accepted'));

        return back()->with('success','Withdraw Accepted Successfully');
    }


    public function withdrawReject(Request $request, Withdrawals $withdraw)
    {
        $request->validate(['reason_of_reject' => 'required']);
        $gs = Generalsetting::first();
        $withdraw->status = 2;
        $withdraw->reject_reason = $request->reason_of_reject;
        $withdraw->save();
        $currency = Currency::findOrFail($withdraw->currency_id);
        $rate = getRate($currency);
        if($withdraw->user_id){
            $user = $withdraw->user;
            //$wallet = Wallet::where('user_id',$withdraw->user_id)->where('user_type',1)->where('currency_id',$withdraw->currency_id)->firstOrFail();
            user_wallet_increment($withdraw->user_id, $withdraw->currency_id, $withdraw->amount);
            $transaction_global_cost = 0;
            $transaction_global_fee = check_global_transaction_fee($withdraw->amount/$rate, $user, 'withdraw');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($withdraw->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            user_wallet_decrement(0, $withdraw->currency_id, $transaction_global_cost*$rate, 9);

            if($user->referral_id != 0)
            {
                $transaction_custom_cost = 0;
                $transaction_custom_fee = check_custom_transaction_fee($withdraw->amount/$rate, User::whereId($withdraw->user_id)->first(), 'withdraw');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($withdraw->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'withdraw_reject_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_decrement($user->referral_id, $withdraw->currency_id, $transaction_custom_cost*$rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $withdraw->currency_id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'withdraw_reject_manager_fee';
                    user_wallet_decrement($user->referral_id, $withdraw->currency_id, $transaction_custom_cost*$rate, 10);
                    $trans_wallet = get_wallet($user->referral_id, $withdraw->currency_id, 10);
                }
                $supervisor_trnx = str_rand();

                $trans = new Transaction();
                $trans->trnx = $supervisor_trnx;
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $withdraw->currency_id;
                $trans->amount      = 0;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->charge      = $transaction_custom_cost*$rate;
                $trans->type        = '-';
                $trans->remark      = $remark;
                $trans->details     = trans('Withdraw request rejected');
                $trans->data        = '{"sender":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
                $trans->save();

                $trans = new Transaction();
                $trans->trnx = $supervisor_trnx;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $withdraw->currency_id;
                $trans->amount      = $transaction_custom_cost*$rate;
                $trans_wallet = get_wallet($withdraw->user_id, $withdraw->currency_id);

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Withdraw request rejected');
                $trans->data        = '{"sender":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
                $trans->save();
            }
            // $wallet->balance += $withdraw->total_amount;
            // $wallet->save();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $withdraw->user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $withdraw->currency->id;
            $trnx->amount      = $withdraw->amount - ($transaction_custom_cost ?? 0)*$rate;

            $trans_wallet = get_wallet($withdraw->user_id, $withdraw->currency_id);
            $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trnx->charge      = 0;
            $trnx->remark      = 'withdraw_reject';
            $trnx->type        = '+';
            $trnx->details     = trans('Withdraw request rejected');
            $trnx->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $trnx->save();

        } else{
            $user = $withdraw->merchant;
            $wallet = Wallet::where('user_id',$withdraw->merchant_id)->where('user_type',2)->where('currency_id',$withdraw->currency_id)->where('wallet_type', 1)->firstOrFail();

            $wallet->balance += $withdraw->amount;
            $wallet->save();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $withdraw->merchant_id;
            $trnx->user_type   = 2;
            $trnx->currency_id = $withdraw->currency->id;

            $trnx->wallet_id   = $wallet->id;

            $trnx->amount      = $withdraw->amount - ($transaction_custom_cost ?? 0)*$rate;
            $trnx->charge      = 0;
            $trnx->remark      = 'withdraw_reject';
            $trnx->type        = '+';
            $trnx->details     = trans('Withdraw request rejected');
            $trnx->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $trnx->save();

        }

        mailSend('reject_withdraw',['amount'=> amount($withdraw->amount,$withdraw->currency->type,2), 'trnx'=> $trnx->trnx,'curr' => $withdraw->currency->code,'method'=>$withdraw->method->name,'reason'=>$withdraw->reject_reason,'date_time'=> dateFormat($trnx->created_at)],$user);
        send_notification($user->id, $withdraw->method->name.' Withdraw  for '.($user->company_name ?? $user->name).' is rejected.'."\n Reject Reason : ".$withdraw->reject_reason."\n Transaction ID : ".$withdraw->trx, route('admin.withdraw.rejected'));

        return back()->with('success','Withdraw Rejected Successfully');
    }
}
