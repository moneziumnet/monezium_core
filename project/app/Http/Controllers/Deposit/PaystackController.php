<?php

namespace App\Http\Controllers\Deposit;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\Deposit;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaystackController extends Controller
{
    public function __construct()
    {

    }

    public function store(Request $request){
        $currency_code = Currency::where('id',$request->currency_id)->first()->code;

        $user = auth()->user();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = Deposit::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = Deposit::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }

        if($dailydeposit > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }

        if($currency_code != "NGN")
        {
            return redirect()->back()->with('unsuccess','Please Select NGN Currency For Paystack.');
        }

        $deposit = new Deposit();
        $deposit['user_id'] = auth()->user()->id;
        $deposit['amount'] = $request->amount;
        $deposit['method'] = $request->method;
        $deposit['status'] = "complete";

        $deposit->save();


        $gs =  Generalsetting::findOrFail(1);
        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/$currency->rate;

        $user = auth()->user();
        $user->income += $amountToAdd;
        $user->save();

        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "Deposti",
                'cname' => $user->name,
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
           $to = $user->email;
           $subject = " You have deposited successfully.";
           $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }

        return redirect()->route('user.deposit.create')->with('success','Deposit amount ('.$request->amount.') successfully!');
    }
}
