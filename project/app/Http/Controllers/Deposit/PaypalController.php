<?php

namespace App\Http\Controllers\Deposit;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Currency;
use App\Classes\GoogleAuthenticator;
use App\Models\PlanDetail;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\Transaction as AppTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

use PayPal\{
    Api\Item,
    Api\Payer,
    Api\Amount,
    Api\Payment,
    Api\ItemList,
    Rest\ApiContext,
    Api\Transaction,
    Api\RedirectUrls,
    Api\PaymentExecution,
    Auth\OAuthTokenCredential
};

class PaypalController extends Controller
{
    private $_api_context;

    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('paypal')->first();
        $paydata = $data->convertAutoData();

        $paypal_conf = \Config::get('paypal');
        $paypal_conf['client_id'] = $paydata['client_id'];
        $paypal_conf['secret'] = $paydata['client_secret'];
        $paypal_conf['settings']['mode'] = $paydata['sandbox_check'] == 1 ? 'sandbox' : 'live';
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
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
        $cancel_url = action('Deposit\PaypalController@cancle');
        $notify_url = action('Deposit\PaypalController@notify');

        $item_name = $settings->title." Deposit";
        $item_number = Str::random(12);
        $item_amount = $request->amount;
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

        $support = ['USD','EUR'];
        if(!in_array($currency_code,$support)){
            return redirect()->back()->with('warning','Please Select USD Or EUR Currency For Paypal.');
        }

        $currency = Currency::whereId($request->currency_id)->first();
        $amountToAdd = $request->amount/getRate($currency);

        $deposit['user_id'] = auth()->user()->id;
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $request->amount ;
        $deposit['method'] = $request->method;
        $deposit['deposit_number'] = $item_number;
        $deposit['status'] = "pending";

        $deposit->save();


        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $item_1 = new Item();
        $item_1->setName($item_name)
            ->setCurrency($currency_code)
            ->setQuantity(1)
            ->setPrice($item_amount);
        $item_list = new ItemList();
        $item_list->setItems(array($item_1));
        $amount = new Amount();
        $amount->setCurrency($currency_code)
            ->setTotal($item_amount);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($item_name.' Via Paypal');
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl($notify_url)
            ->setCancelUrl($cancel_url);
        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));


        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            return redirect()->back()->with('unsuccess',$ex->getMessage());
        }
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        Session::put('deposit_data',$request->all());
        Session::put('paypal_payment_id', $payment->getId());
        Session::put('deposit_number',$item_number);

        if (isset($redirect_url)) {
            return Redirect::away($redirect_url);
        }


        return redirect()->back()->with('unsuccess','Unknown error occurred');

        if (isset($redirect_url)) {
            return Redirect::away($redirect_url);
        }
        return redirect()->back()->with('unsuccess','Unknown error occurred');

    }

    public function notify(Request $request)
    {

        $user = auth()->user();
        $deposit_data = Session::get('deposit_data');

        $payment_id = Session::get('paypal_payment_id');
        if (empty( $request['PayerID']) || empty( $request['token'])) {
            return redirect()->back()->with('error', 'Payment Failed');
        }

        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request['PayerID']);


        $deposit_number = Session::get('deposit_number');
        $result = $payment->execute($execution, $this->_api_context);

        if ($result->getState() == 'approved') {
            $resp = json_decode($payment, true);

                $deposit = Deposit::where('deposit_number',$deposit_number)->where('status','pending')->first();
                $data['txnid'] = $resp['transactions'][0]['related_resources'][0]['sale']['id'];
                $data['status'] = "complete";
                $deposit->update($data);

                $gs =  Generalsetting::findOrFail(1);

                $currency = Currency::findOrFail($deposit->currency_id);
                user_wallet_increment($user->id, $deposit->currency_id, $deposit->amount);


                $trans = new AppTransaction();
                $trans->trnx = $deposit->deposit_number;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $deposit->currency_id;
                $trans->amount      = $deposit->amount;
                $trans->charge      = 0;
                $trans_wallet = get_wallet($user->id, $deposit->currency_id);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->type        = '+';
                $trans->remark      = 'Deposit';
                $trans->data        = '{"sender":"Paypal", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "description": "Paypal / '.$deposit->deposit_number.'"}';
                $trans->details     = trans('Deposit Paypal complete');

                mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency->code, 'trnx' => $deposit->deposit_number ,'date_time'=>$trans->created_at ,'type' => 'Paypal' ], $user);
                send_notification($user->id, 'Paypal Deposit for '.($user->company_name ?? $user->name).'is approved '."\n Amount is ".$currency->symbol.$deposit->amount."\n Transaction ID : ".$deposit->deposit_number, route('admin.deposits.index'));
                // $trans->email = $user->email;
                // $trans->amount = $deposit->amount;
                // $trans->type = "Deposit";
                // $trans->profit = "plus";
                // $trans->txnid = $deposit->deposit_number;
                // $trans->user_id = $user->id;
                $trans->save();

                Session::forget('deposit_data');
                Session::forget('paypal_payment_id');
                Session::forget('deposit_number');

                return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$deposit->amount.' ('.$currency->code.') successfully!');
        }

    }
}
