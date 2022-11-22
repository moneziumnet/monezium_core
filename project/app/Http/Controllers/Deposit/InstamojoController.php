<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentGateway;
use App\Models\PlanDetail;
use App\Models\Generalsetting;
use App\Classes\GoogleAuthenticator;
use App\Classes\GeniusMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Classes\Instamojo;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Transaction;

class InstamojoController extends Controller
{
    public function store(Request $request)
    {
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
        $request->currency_code = $currency_code;
        $input = $request->all();

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

        $data = PaymentGateway::whereKeyword('instamojo')->first();
        $gs = Generalsetting::first();
        $total =  $request->amount;
        $paydata = $data->convertAutoData();

        if($currency_code != "INR")
        {
            return redirect()->back()->with('warning',__('Please Select INR Currency For This Payment.'));
        }

        $user = auth()->user();
        $order['item_name'] = $gs->title." Deposit";
        $order['item_number'] = Str::random(12);
        $order['item_amount'] = $total;

        $cancel_url = route('deposit.paypal.cancle');
        $notify_url = route('deposit.instamojo.notify');

        if($paydata['sandbox_check'] == 1){
            $api = new Instamojo($paydata['key'], $paydata['token'], 'https://test.instamojo.com/api/1.1/');
        }
        else {
            $api = new Instamojo($paydata['key'], $paydata['token']);
        }

        try {
            $response = $api->paymentRequestCreate(array(
                "purpose" => $order['item_name'],
                "amount" => $order['item_amount'],
                "send_email" => true,
                "email" => $user->email,
                "redirect_url" => $notify_url
        ));
        $redirect_url = $response['longurl'];

        Session::put('input_data',$input);
        Session::put('order_data',$order);
        Session::put('order_payment_id', $response['id']);

        return redirect($redirect_url);

        }
        catch (Exception $e) {
            return redirect($cancel_url)->with('unsuccess','Error: ' . $e->getMessage());
        }
    }


    public function notify(Request $request)
    {
        $input = Session::get('input_data');
        $order_data = Session::get('order_data');

        $input_data = $request->all();
        $user = auth()->user();

        $deposit = new Deposit();

        $payment_id = Session::get('order_payment_id');
        if($input_data['payment_status'] == 'Failed'){
            return redirect()->back()->with('unsuccess','Something Went wrong!');
        }

        if ($input_data['payment_request_id'] == $payment_id) {

            $currency = Currency::where('id',$input['currency_id'])->first();
            $amountToAdd = $input['amount']/getRate($currency);

            $deposit['deposit_number'] = $order_data['item_number'];
            $deposit['user_id'] = auth()->user()->id;
            $deposit['currency_id'] = $input['currency_id'];
            $deposit['amount'] = $amountToAdd;
            $deposit['method'] = $input['method'];
            $deposit['txnid'] = $payment_id;
            $deposit['status'] = "complete";

            $deposit->save();


            $gs =  Generalsetting::findOrFail(1);

            $user = auth()->user();
            user_wallet_increment($user->id, $input['currency_id'], $amountToAdd);


            $trans = new Transaction();
            $trans->trnx = $order_data['item_number'];
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans_wallet = get_wallet($user->id, $input['currency_id']);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->amount      = $amountToAdd;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Deposit_create';
            $trans->data        = '{"sender":"InstamojoPay System", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'"}';
            $trans->details     = trans('Deposit Instamojo complete');

            $trans->save();


               $to = $user->email;
               $subject = " You have deposited successfully.";
               $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
               $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
               mail($to,$subject,$msg,$headers);

            return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$input['amount'].' ('.$input['currency_code'].') successfully!');

        }
        return redirect()->route('user.deposit.create')->with('unsuccess','Something Went wrong!');
    }
}
