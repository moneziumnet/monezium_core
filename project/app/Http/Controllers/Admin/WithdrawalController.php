<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Models\Withdrawals;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Charge;

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


      @mailSend('accept_withdraw',['amount'=>amount($withdraw->amount,$withdraw->currency->type,2), 'method' => $withdraw->withdraw->name,'trnx'=> $withdraw->trx,'curr' => $withdraw->currency->code,'method'=>$withdraw->method->name,'charge'=> amount($withdraw->charge,$withdraw->currency->type,2),'data_time'=> dateFormat($withdraw->updated_at)], $withdraw->user);

        return back()->with('success','Withdraw Accepted Successfully');
    }


    public function withdrawReject(Request $request, Withdrawals $withdraw)
    {
        $request->validate(['reason_of_reject' => 'required']);

        $withdraw->status = 2;
        $withdraw->reject_reason = $request->reason_of_reject;
        $withdraw->save();

        if($withdraw->user_id){
            $user = $withdraw->user;
            //$wallet = Wallet::where('user_id',$withdraw->user_id)->where('user_type',1)->where('currency_id',$withdraw->currency_id)->firstOrFail();
            user_wallet_increment($withdraw->user_id, $withdraw->currency_id, $withdraw->amount);
            $explode = explode(',',User::whereId($withdraw->user_id)->first()->user_type);

            if($user->referral_id != 0)
            {
                $transaction_custom_cost = 0;
                $transaction_custom_fee = check_custom_transaction_fee($withdraw->amount, User::whereId($withdraw->user_id)->first(), 'withdraw');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($withdraw->amount/100) * $transaction_custom_fee->data->percent_charge;
                }
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_decrement($user->referral_id, $withdraw->currency_id, $transaction_custom_cost, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    user_wallet_decrement($user->referral_id, $withdraw->currency_id, $transaction_custom_cost, 10);
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $withdraw->currency_id;
                $trans->amount      = $transaction_custom_cost;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'withdraw_reject_supervisor_fee';
                $trans->details     = trans('Withdraw request rejected');
                $trans->save();
            }
            // $wallet->balance += $withdraw->total_amount;
            // $wallet->save();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $withdraw->user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $withdraw->currency->id;
            $trnx->amount      = $withdraw->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'withdraw_reject';
            $trnx->type        = '+';
            $trnx->details     = trans('Withdraw request rejected');
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
            $trnx->amount      = $withdraw->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'withdraw_reject';
            $trnx->type        = '+';
            $trnx->details     = trans('Withdraw request rejected');
            $trnx->save();

        }

        @mailSend('reject_withdraw',['amount'=> amount($withdraw->amount,$withdraw->currency->type,2), 'method' => $withdraw->withdraw->name,'trnx'=> $trnx->trnx,'curr' => $withdraw->currency->code,'method'=>$withdraw->method->name,'reason'=>$withdraw->reject_reason,'data_time'=> dateFormat($trnx->created_at)],$user);

        return back()->with('success','Withdraw Rejected Successfully');
    }
}
