<?php

namespace App\Http\Controllers\Deposit;


use App\Http\Controllers\Controller;
use App\Models\Currency;
use Mollie\Laravel\Facades\Mollie;
use App\Classes\GoogleAuthenticator;
use App\Models\Generalsetting;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Transaction;
use Carbon\Carbon;
use Session;
use Auth;
use Str;

class MollieController extends Controller
{
    public function store(Request $request){
        $support = [
            'AED',
            'AUD',
            'BGN',
            'BRL',
            'CAD',
            'CHF',
            'CZK',
            'DKK',
            'EUR',
            'GBP',
            'HKD',
            'HRK',
            'HUF',
            'ILS',
            'ISK',
            'JPY',
            'MXN',
            'MYR',
            'NOK',
            'NZD',
            'PHP',
            'PLN',
            'RON',
            'RUB',
            'SEK',
            'SGD',
            'THB',
            'TWD',
            'USD',
            'ZAR'
        ];
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
        $currency_code = Currency::where('id',$request->currency_id)->first()->code;
        if(!in_array($currency_code,$support)){
            return redirect()->back()->with('warning','Please Select USD Or EUR Currency For Paypal.');
        }

        $item_amount = $request->amount;
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

        $item_name = "Deposit via Molly Payment";


        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => 'USD',
                'value' => ''.sprintf('%0.2f', $item_amount).'',
            ],
            'description' => $item_name ,
            'redirectUrl' => route('deposit.molly.notify'),
            ]);


        Session::put('molly_data',$input);
        Session::put('payment_id',$payment->id);
        $payment = Mollie::api()->payments()->get($payment->id);

        return redirect($payment->getCheckoutUrl(), 303);
    }


    public function notify(Request $request){

        $input = Session::get('molly_data');
        $payment = Mollie::api()->payments()->get(Session::get('payment_id'));

        if($payment->status == 'paid'){
            $currency = Currency::where('id',$input['currency_id'])->first();
            $amountToAdd = $input['amount']/getRate($currency);

            $deposit = new Deposit();
            $deposit['deposit_number'] = Str::random(12);
            $deposit['user_id'] = auth()->id();
            $deposit['currency_id'] = $input['currency_id'];
            $deposit['amount'] = $input['amount'];
            $deposit['method'] = $input['method'];
            $deposit['status'] = "complete";
            $deposit['txnid'] = $payment->id;
            $deposit->save();

            $user = auth()->user();
            user_wallet_increment($user->id, $input['currency_id'], $input['amount']);


            $trans = new Transaction();
            $trans->trnx = $deposit->deposit_number;
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency->id;
            $trans->amount      = $input['amount'];
            $trans_wallet = get_wallet($user->id, $input['currency_id']);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Deposit';
            $trans->data        = '{"sender":"MobilePay System", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "description": "MobilePay System / '.$deposit->deposit_number.'"}';
            $trans->details     = trans('Deposit MobilePay complete');

            // $trans->email = $user->email;
            // $trans->amount = $amountToAdd;
            // $trans->type = "Deposit";
            // $trans->profit = "plus";
            // $trans->txnid = $deposit->deposit_number;
            // $trans->user_id = $user->id;
            $trans->save();


            $gs =  Generalsetting::findOrFail(1);
            $user = auth()->user();
            mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency->code, 'trnx' => $deposit->deposit_number ,'date_time'=>$trans->created_at ,'type' => 'MobilePay System' ], $user);

            Session::forget('molly_data');
            return redirect()->route('user.deposit.create')->with('success','Deposit amount ('.$input['amount'].') successfully!');
        }
        else {
            return redirect()->route('user.deposit.create')->with('warning','Something Went wrong!');
        }

        return redirect()->route('user.deposit.create')->with('warning','Something Went wrong!');
    }
}
