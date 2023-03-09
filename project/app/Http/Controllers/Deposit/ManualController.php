<?php

namespace App\Http\Controllers\Deposit;


use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Classes\GoogleAuthenticator;
use Illuminate\Support\Str;

class ManualController extends Controller
{
    public function store(Request $request){

        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/getRate($currency);

        $user = auth()->user();
        if($user->paymentcheck('Payment Gateway Incoming')) {
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
            else{
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
        }
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = Deposit::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = Deposit::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }

        if($dailydeposit > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }

        $deposit = new Deposit();
        $deposit['deposit_number'] = Str::random(12);
        $deposit['user_id'] = auth()->id();
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $request->amount;
        $deposit['method'] = $request->method;
        $deposit['txnid'] = $request->txn_id4;
        $deposit['status'] = "pending";
        $deposit->save();


            mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency->code, 'date_time'=>$deposit->created_at ,'type' => 'Manual', 'method'=>'Payment Gateway' ], $user);

        return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }
}
