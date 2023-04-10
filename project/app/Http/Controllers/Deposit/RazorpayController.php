<?php

namespace App\Http\Controllers\Deposit;


use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\PlanDetail;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('razorpay')->first();
        $paydata = $data->convertAutoData();
        $this->keyId = $paydata['key'];
        $this->keySecret = $paydata['secret'];
        $this->displayCurrency = 'INR';
        $this->api = new Api($this->keyId, $this->keySecret);
    }


    public function store(Request $request)
    {
        $currency_code = Currency::where('id',$request->currency_id)->first()->code;

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

        if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }

        if($dailydeposit > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }

        $request->currency_code = $currency_code;
        if($currency_code != "INR")
        {
            return redirect()->back()->with('warning','Please Select INR Currency For Rezorpay.');
        }

        $settings = Generalsetting::findOrFail(1);

        $input = $request->all();
        $item_name = $settings->title." Deposit";
        $item_number = Str::random(12);
        $item_amount = $request->amount;

        $order['item_name'] = $item_name;
        $order['item_number'] = $item_number;
        $order['item_amount'] = round($item_amount,2);
        $cancel_url = route('user.dashboard');
        $notify_url = route('deposit.razorpay.notify');


        $orderData = [
            'receipt'         => $order['item_number'],
            'amount'          => $order['item_amount'] * 100, // 2000 rupees in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];

        $razorpayOrder = $this->api->order->create($orderData);

        $input['user_id'] = auth()->user()->id;

        Session::put('input_data',$input);
        Session::put('order_data',$order);
        Session::put('order_payment_id', $razorpayOrder['id']);

        $displayAmount = $amount = $orderData['amount'];

        if ($this->displayCurrency !== 'INR')
        {
            $url = "https://api.fixer.io/latest?symbols=$this->displayCurrency&base=INR";
            $exchange = json_decode(file_get_contents($url), true);

            $displayAmount = $exchange['rates'][$this->displayCurrency] * $amount / 100;
        }

        $checkout = 'automatic';

        if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true))
        {
            $checkout = $_GET['checkout'];
        }

        $data = [
            "key"               => $this->keyId,
            "amount"            => $amount,
            "name"              => $order['item_name'],
            "description"       => $order['item_name'],
            "prefill"           => [
                "name"              => $request->customer_name,
                "email"             => $request->customer_email,
                "contact"           => $request->customer_phone,
            ],
            "notes"             => [
                "address"           => $request->customer_address,
                "merchant_order_id" => $order['item_number'],
            ],
            "theme"             => [
                "color"             => "{{$settings->colors}}"
            ],
            "order_id"          => $razorpayOrder['id'],
        ];

        if ($this->displayCurrency !== 'INR')
        {
            $data['display_currency']  = $this->displayCurrency;
            $data['display_amount']    = $displayAmount;
        }

        $json = json_encode($data);
        $displayCurrency = $this->displayCurrency;

        return view( 'frontend.razorpay-checkout', compact( 'data','displayCurrency','json','notify_url' ) );
    }

    public function notify(Request $request)
    {
        $input = Session::get('input_data');
        $order_data = Session::get('order_data');
        $input_data = $request->all();

        $payment_id = Session::get('order_payment_id');

        $success = true;

        if (empty($input_data['razorpay_payment_id']) === false)
        {

            try
            {
                $attributes = array(
                    'razorpay_order_id' => $payment_id,
                    'razorpay_payment_id' => $input_data['razorpay_payment_id'],
                    'razorpay_signature' => $input_data['razorpay_signature']
                );

                $this->api->utility->verifyPaymentSignature($attributes);
            }
            catch(SignatureVerificationError $e)
            {
                $success = false;
            }
        }

        if ($success === true){
            $currency = Currency::where('id',$request->currency_id)->first();
            $amountToAdd = $input['amount']/getRate($currency);

            $deposit = new Deposit();
            $deposit['deposit_number'] = $order_data['item_number'];
            $deposit['user_id'] = auth()->user()->id;
            $deposit['currency_id'] = $request->currency_id;
            $deposit['amount'] = $input['amount'];
            $deposit['method'] = $input['method'];
            $deposit['status'] = "complete";
            $deposit['txnid'] = $payment_id;
            $deposit->save();



            $gs =  Generalsetting::findOrFail(1);

            $user = auth()->user();
            $currency_id = $request->currency_id?$request->currency_id:Currency::whereIsDefault(1)->first()->id;
            user_wallet_increment($user->id, $currency_id, $input['amount']);


            $trans = new Transaction();
            $trans->trnx = $deposit->deposit_number;
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;
            $trans_wallet = get_wallet($user->id, $currency_id);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->amount      = $input['amount'];
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Deposit';
            $trans->data        = '{"sender":"Razorpay System", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "description": "Razorpay System / '.$deposit->deposit_number.'"}';
            $trans->details     = trans('Deposit Razorpay complete');

            // $trans->email = $user->email;
            // $trans->amount = $amountToAdd;
            // $trans->type = "Deposit";
            // $trans->profit = "plus";
            // $trans->txnid = $deposit->deposit_number;
            // $trans->user_id = $user->id;
            $trans->save();
            mailSend('deposit_approved',['amount'=>$deposit->amount, 'curr' => $currency->code, 'trnx' => $deposit->deposit_number ,'date_time'=>$trans->created_at ,'type' => 'Razorpay' ], $user);
            send_notification($user->id, 'Razorpay Deposit  for '.($user->company_name ?? $user->name).' is approved. Please check .', route('admin.deposits.index'));




            return redirect()->route('user.deposit.create')->with('success','Deposit amount '.$input['amount'].' ('.$currency->code.') successfully!');

        }
        return redirect()->back()->with('warning','Something Went wrong!');
    }
}
