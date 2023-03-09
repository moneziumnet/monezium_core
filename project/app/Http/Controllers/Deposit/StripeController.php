<?php

namespace App\Http\Controllers\Deposit;


use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\PlanDetail;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Classes\GoogleAuthenticator;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\Error\Card;
use Carbon\Carbon;
use Input;
use Redirect;
use URL;
use Validator;
use Config;

class StripeController extends Controller
{
    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('Stripe')->first();
        $paydata = $data->convertAutoData();

        Config::set('services.stripe.key', $paydata['key']);
        Config::set('services.stripe.secret', $paydata['secret']);
    }

    public function store(Request $request){
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
        $settings = Generalsetting::findOrFail(1);
        $deposit = new Deposit();
        $item_name = $settings->title." Deposit";
        $item_number = Str::random(4).time();
        $item_amount = $request->amount;
        $currency_code = Currency::where('id',$request->currency_id)->first()->code;
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


        $support = ['USD'];
        if(!in_array($currency_code,$support)){
            return redirect()->back()->with('warning','Please Select USD Or EUR Currency For Paypal.');
        }
        $user = auth()->user();

        $validator = Validator::make($request->all(),[
                        'cardNumber' => 'required',
                        'cardCVC' => 'required',
                        'month' => 'required',
                        'year' => 'required',
                    ]);

        if ($validator->passes()) {

            $stripe = Stripe::make(Config::get('services.stripe.secret'));
            try{
                $token = $stripe->tokens()->create([
                    'card' =>[
                            'number' => $request->cardNumber,
                            'exp_month' => $request->month,
                            'exp_year' => $request->year,
                            'cvc' => $request->cardCVC,
                        ],
                    ]);
                if (!isset($token['id'])) {
                    return back()->with('error','Token Problem With Your Token.');
                }

                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => $currency_code,
                    'amount' => $item_amount,
                    'description' => $item_name,
                    ]);

                if ($charge['status'] == 'succeeded') {
                    $currency = Currency::where('id',$request->currency_id)->first();
                    $amountToAdd = $request->amount;

                    $deposit['deposit_number'] = Str::random(12);
                    $deposit['user_id'] = auth()->id();
                    $deposit['currency_id'] = $request->currency_id;
                    $deposit['amount'] = $amountToAdd;
                    $deposit['method'] = $request->method;
                    $deposit['txnid'] = $charge['balance_transaction'];
                    $deposit['charge_id'] = $charge['id'];
                    $deposit['status'] = "complete";
                    $deposit->save();

                    $gs =  Generalsetting::findOrFail(1);

                    $user = auth()->user();
                    $currency_id = $request->currency_id?$request->currency_id:Currency::whereIsDefault(1)->first()->id;
                    user_wallet_increment($user->id, $currency_id, $amountToAdd);


                    $trans = new Transaction();
                    $trans->trnx = $deposit->deposit_number;
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = $request->currency_id;
                    $trans_wallet = get_wallet($user->id, $currency_id);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->amount      = $amountToAdd;
                    $trans->charge      = 0;
                    $trans->type        = '+';
                    $trans->remark      = 'Deposit';
                    $trans->data        = '{"sender":"Stripe System", "receiver":"'.($user->company_name ?? $user->name).'"}';
                    $trans->details     = trans('Deposit Stripe complete');

                    // $trans->email = $user->email;
                    // $trans->amount = $amountToAdd;
                    // $trans->type = "Deposit";
                    // $trans->profit = "plus";
                    // $trans->txnid = $deposit->deposit_number;
                    // $trans->user_id = $user->id;
                    $trans->save();


                       mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency->code, 'trnx' => $deposit->deposit_number ,'date_time'=>$trans->created_at ,'type' => 'Razorpay' ], $user);

                    return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$request->amount.' '.$currency->code.' successfully!');
                }

            }catch (Exception $e){
                return back()->with('unsuccess', $e->getMessage());
            }catch (\Cartalyst\Stripe\Exception\CardErrorException $e){
                return back()->with('unsuccess', $e->getMessage());
            }catch (\Cartalyst\Stripe\Exception\MissingParameterException $e){
                return back()->with('unsuccess', $e->getMessage());
            }
        }
        return back()->with('unsuccess', 'Please Enter Valid Credit Card Informations.');
    }
}
