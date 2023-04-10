<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\BankPlan;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request){

        $userBalance = user_wallet_balance(auth()->id(),$request->currency_id);
        $plan = BankPlan::findOrFail($request->bank_plan_id);
        $gs = Generalsetting::first();
        if($plan->amount > $userBalance)
        {
            return redirect()->route('user.dashboard')->with('error', 'Your Balance not Available.');
        }

        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $request->currency_id;
        $trnx->amount      = $plan->amount;
        $trans_wallet = get_wallet(auth()->id(), $request->currency_id);
        $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trnx->charge      = 0;
        $trnx->remark      = 'upgrade_plan';
        $trnx->type        = '-';
        $trnx->details     = trans('Upgrade Plan');
        $trnx->data        = '{"sender":"'.(User::findOrFail(auth()->id())->company_name ?? User::findOrFail(auth()->id())->name).'", "receiver":"'.$gs->disqus.'"}';
        $trnx->save();
        user_wallet_decrement(auth()->id(), $request->currency_id, $plan->amount);
        user_wallet_increment(0, $request->currency_id, $plan->amount, 9);
        $user = User::findorFail(auth()->id());
        if($user){
            $user->bank_plan_id = $plan->id;
            $user->plan_end_date = now()->addDays($plan->days);
            $user->update();
        }
        $currency = Currency::findOrFail($request->currency_id);
        mailSend('plan_upgrade',['amount' => $plan->amount, 'curr' => $currency->code, 'date_time' => $trnx->created_at], auth()->user());
        send_notification($user->id, ($user->company_name ?? $user->name).' Pricing Plan is updated. Please check .', route('admin-user-pricingplan', $user->id));

        return redirect()->route('user.dashboard')->with('message','Bank Plan Updated');
    }
}
