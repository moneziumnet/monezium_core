<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\FdrPlan;
use App\Models\Transaction;
use App\Models\UserFdr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFdrController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



    public function fdrAmount(Request $request){
        $plan = FdrPlan::whereId($request->planId)->first();
        $amount = $request->amount;

        if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
            $data['data'] = $plan;
            $data['fdrAmount'] = $amount;
            $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();

            return view('user.fdr.apply',$data);
        }else{
            // return redirect()->back()->with('warning','Request Money should be between minium and maximum amount!');
            return redirect()->route('user.invest.index')->with('warning','Request Money should be between minium and maximum amount!');
        }
    }

    public function finish(Request $request){
        $fdr = UserFdr::whereId($request->plan_Id)->first();
        if($fdr){
            // user_wallet_decrement($fdr->user_id, $fdr->currency_id, $fdr->fdr_amount, 4);
            $currency = $fdr->currency->id;
            user_wallet_increment($fdr->user_id, $currency, $fdr->amount, 3);
            user_wallet_decrement($fdr->user_id, $currency, $fdr->amount, 3);
            $fdr->next_profit_time = NULL;
            $fdr->status = 2;
            $fdr->update();

            return redirect()->back()->with('message','Finish Requesting Successfully');
        }else {
            return redirect()->back()->with('warning','There is not your FDR plan');
        }
    }

    public function fdrRequest(Request $request){
        // $user = auth()->user();
        // dd($request);
        // if($user->balance >= $request->fdr_amount){
        if(user_wallet_balance(auth()->id(),$request->input('currency_id'), 3)  >= $request->fdr_amount){

            $data = new UserFdr();
            $plan = FdrPlan::findOrFail($request->plan_id);

            $data->transaction_no = Str::random(4).time();
            $data->user_id = auth()->id();
            $data->fdr_plan_id = $plan->id;
            $data->amount = $request->fdr_amount;
            $data->profit_type = $plan->interval_type;
            $data->profit_amount = $request->profit_amount;
            $data->interest_rate = $plan->interest_rate;
            $data->currency_id = $request->currency_id;

            if($plan->interval_type == 'partial'){
                $data->next_profit_time = Carbon::now()->addDays($plan->interest_interval);
            }
            $data->matured_time = Carbon::now()->addDays($plan->matured_days);
            $data->status = 1;
            $data->save();

            //$user->decrement('balance',$request->fdr_amount);
            user_wallet_decrement(auth()->id(),$request->input('currency_id'),$request->fdr_amount, 3);
            user_wallet_increment(auth()->id(),$request->input('currency_id'),$request->fdr_amount, 3);

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = auth()->id();
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;
            $trans->amount      = $request->fdr_amount;
            $trans_wallet = get_wallet(auth()->id(),$request->input('currency_id'),3);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Fdr_create';
            $trans->details     = trans('Fdr created');

            // $trans->email = auth()->user()->email;
            // $trans->amount = $request->fdr_amount;
            // $trans->type = "Fdr";
            // $trans->profit = "minus";
            // $trans->txnid = $data->transaction_no;
            // $trans->user_id = auth()->id();
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"System Account"}';
            $trans->save();

            return redirect()->route('user.invest.index')->with('success','Loan Requesting Successfully');
        }else{
            // return redirect()->back()->with('warning','You Don,t have sufficient balance');
            return redirect()->route('user.invest.index')->with('warning','You Don,t have sufficient balance');
        }
    }
}
