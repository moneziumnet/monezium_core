<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\CryptoDeposit;
use App\Models\Currency;
use App\Models\DepositBank;
use App\Models\Generalsetting;
use App\Models\Transaction as AppTransaction;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\SubInsBank;
use App\Models\MerchantShop;
use App\Models\MerchantSetting;
use App\Models\MerchantWallet;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Support\Str;
use Config;
use Input;
use URL;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
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

class AccessController extends Controller
{
    public function index(Request $request) {
        if($request->site_key) {
            $site_key = $request->site_key;
            $amount = $request->amount;
            $currency_id = $request->currency;
            $shop_key = $request->shop_key;
        } else {
            $site_key = Session::get('site_key');
            $amount = Session::get('amount');
            $currency_id = Session::get('currency_id');
            $shop_key = Session::get('shop_key');
        }

        $currency = Currency::where('code', $currency_id)->first();
        $user_api = UserApiCred::where('access_key', $site_key)->first();
        if($user_api) {
            $shop = MerchantShop::where('site_key', $shop_key)->where('merchant_id', $user_api->user_id)->first();
            if(!$shop) {
                return view('api.error');
            }
            Session::put('site_key', $site_key);
            Session::put('currency_id', $currency_id);
            Session::put('amount', $amount);
            Session::put('shop_key', $shop_key);
            $user = $user_api->user;
            $bankaccounts = BankAccount::where('user_id', $user->id)->where('currency_id', $currency->id)->get();

            $crypto_ids =  MerchantWallet::where('merchant_id', $user->id)->where('shop_id', $shop->id)->pluck('currency_id')->toArray();

            $cryptolist = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();
            $gateways = MerchantSetting::where('user_id', $user_api->user_id)->get();
            return view('api.payment', compact('bankaccounts','cryptolist','amount','currency','user', 'shop_key', 'gateways'));
        } else {
            return view('api.error');
        }
    }

    public function login() {
        return view('api.login');
    }

    public function login_submit(Request $request)
    {
        $rules = [
            'email'   => 'required|email',
            'password' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return back()->with('error', $errors['email'][0] ?? $errors['password'][0]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {

            if(Auth::guard('web')->user()->is_banned == 1) {
              Auth::guard('web')->logout();
              return back()->with("error", "You are Banned From this system!" );
            }

            if(Auth::guard('web')->user()->email_verified == 'No') {
              Auth::guard('web')->logout();
              return back()->with("error", "Your Email is not Verified!" );
            }

            return redirect(route('api.pay.index'))->with('message', 'Login successfully.');
        }

        return back()->with("error", "Credentials Doesn't Match !" );
    }

    public function crypto_pay(Request $request) {
        $pre_currency = Currency::findOrFail($request->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['total_amount'] = $request->amount;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
        $shop = MerchantShop::where('site_key', $request->shop_key)->where('merchant_id', $request->user_id)->first();

        $data['wallet'] =  MerchantWallet::where('merchant_id', $request->user_id)->where('shop_id', $shop->id)->where('currency_id', $select_currency->id)->first();
        $data['shop_key'] = $request->shop_key;

        if(!$data['wallet']) {
            return redirect()->back()->with('error', $select_currency->code .' Crypto wallet does not existed in sender.');
        }
        return view('api.crypto', $data);
    }

    public function gateway_pay(Request $request) {
        $currency = Currency::findOrFail($request->currency_id);
        $merchant_setting = MerchantSetting::where('id', $request->link_pay_submit)->where('user_id', $request->user_id)->first();
        $amount = $request->amount;
        $shop_key = $request->shop_key;
        
        if(!$merchant_setting) {
            return redirect()->back()->with('error', 'Payment Gateway does not existed in Merchant .');
        }

        return view('api.gateway', compact('currency', 'merchant_setting', 'amount', 'shop_key'));
    }

    public function pay_submit(Request $request) {
        $shop = MerchantShop::where('site_key', $request->shop_key)->where('merchant_id', $request->user_id)->first();

        if($request->payment == 'gateway'){
            $merchant_setting = MerchantSetting::where('id', $request->gateway_id)->where('user_id', $request->user_id)->first();
            if(!$merchant_setting) {
                return response()->json([
                    'type' => 'mt_payment_error',
                    'payload' => 'This Payment Gateway does not existed in Merchant .'
                ]);
            }
            if($merchant_setting->keyword == 'paypal') {

                $paydata = $merchant_setting->information;
        
                $paypal_conf = \Config::get('paypal');
                $paypal_conf['client_id'] = $paydata['client_id'];
                $paypal_conf['secret'] = $paydata['client_secret'];
                $paypal_conf['settings']['mode'] = $paydata['sandbox_check'] == 1 ? 'sandbox' : 'live';
                $_api_context = new ApiContext(new OAuthTokenCredential(
                    $paypal_conf['client_id'],
                    $paypal_conf['secret'])
                );
                $_api_context->setConfig($paypal_conf['settings']);


                
                $item_name = $shop->name." Merchant Payment";
                $item_number = Str::random(12);

                $notify_url = route('api.pay.paypal.success', $shop->id, $item_number);
                $cancel_url = route('api.pay.paypal.cancel', $shop->id, $item_number);
                
                $item_amount = $request->amount;
                $currency_code = Currency::where('id',$request->currency_id)->first()->code;

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
                    $payment->create($_api_context);
                } catch (\PayPal\Exception\PPConnectionException $ex) {
                    return response()->json([
                        'type' => 'mt_payment_error',
                        'payload' => $ex->getMessage()
                    ]);
                }
                foreach ($payment->getLinks() as $link) {
                    if ($link->getRel() == 'approval_url') {
                        $redirect_url = $link->getHref();
                        break;
                    }
                }

                Session::put('paypal_payment_id', $payment->getId());
                Session::put('deposit_number',$item_number);
                // Session::put('user_id',$item_number);

                if (isset($redirect_url)) {
                    // return redirect()->away($redirect_url);
                    return response()->json([
                        'type' => 'login',
                        'payload' => ['reference' => $item_number, 'message' => 'Paypal Payment is Pending', 'status' => 'pending', 'redirect_url' => $redirect_url]
                    ]);
                    // return Redirect::away($redirect_url);
                }

                return response()->json([
                    'type' => 'mt_payment_error',
                    'payload' => 'Unknown error occurred'
                ]);
            }
            else if ($merchant_setting->keyword == 'stripe') {
                $paydata = $merchant_setting->information;
                Config::set('services.stripe.key', $paydata['key']);
                Config::set('services.stripe.secret', $paydata['secret']);

                $item_name = $shop->name." Merchant Payment";
                $item_number = Str::random(4).time();
                $item_amount = $request->amount;
                $currency_code = Currency::where('id',$request->currency_id)->first()->code;

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
                            return response()->json([
                                'type' => 'mt_payment_error',
                                'payload' => 'Token Problem With Your Token.'
                            ]);
                        }
        
                        $charge = $stripe->charges()->create([
                            'card' => $token['id'],
                            'currency' => $currency_code,
                            'amount' => $item_amount,
                            'description' => $item_name,
                            ]);
        
                        if ($charge['status'] == 'succeeded') {
                            $rcvWallet = MerchantWallet::where('merchant_id', $request->user_id)->where('shop_id', $shop->id)->where('currency_id', $request->currency_id)->first();

                            if(!$rcvWallet){
                                $gs = Generalsetting::first();
                                $rcvWallet =  MerchantWallet::create([
                                    'merchant_id'     => $request->user_id,
                                    'currency_id' => $request->currency_id,
                                    'shop_id' => $shop->id,
                                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                                ]);

                            }

                            $rcvWallet->balance += $request->amount;
                            $rcvWallet->update();

                            $rcvTrnx              = new AppTransaction();
                            $rcvTrnx->trnx        = str_rand();
                            $rcvTrnx->user_id     = $request->user_id;
                            $rcvTrnx->user_type   = 1;
                            $rcvTrnx->currency_id = $request->currency_id;
                            $rcvTrnx->amount      = $request->amount;
                            $rcvTrnx->charge      = 0;
                            $rcvTrnx->remark      = 'merchant_api_payment';
                            $rcvTrnx->type        = '+';
                            $rcvTrnx->details     = trans('Receive Merchant Payment');
                            $rcvTrnx->data        = '{"sender":"Stripe System", "receiver":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'"}';
                            $rcvTrnx->save();
                            return response()->json([
                                'type' => 'mt_payment_success',
                                'payload' => ['reference' => $rcvTrnx->trnx, 'message' => 'Gateway Stripe Payment completed', 'status' => 'complete']
                            ]);
                        }
        
                    }catch (Exception $e){
                        return response()->json([
                            'type' => 'mt_payment_error',
                            'payload' => $e->getMessage()
                        ]);
                    }catch (\Cartalyst\Stripe\Exception\CardErrorException $e){
                        return response()->json([
                            'type' => 'mt_payment_error',
                            'payload' => $e->getMessage()
                        ]);
                    }catch (\Cartalyst\Stripe\Exception\MissingParameterException $e){
                        return response()->json([
                            'type' => 'mt_payment_error',
                            'payload' => $e->getMessage()
                        ]);
                    }
                }


                return response()->json([
                    'type' => 'mt_payment_error',
                    'payload' => 'Please Enter Valid Credit Card Informations.'
                ]);
            }
        } else if($request->payment == 'bank_pay'){

            $bankaccount = BankAccount::where('id', $request->bank_account)->first();

            $deposit = new DepositBank();
            $deposit['deposit_number'] = $request->deposit_no;
            $deposit['user_id'] = $request->user_id;
            $deposit['currency_id'] = $request->currency_id;
            $deposit['amount'] = $request->amount;
            $deposit['sub_bank_id'] = $bankaccount->subbank_id;
            $deposit['txnid'] = $request->deposit_no;
            $deposit['purpose'] = 'merchant_shop';
            $deposit['purpose_data'] = $shop->id;
            $deposit['status'] = "pending";
            $deposit->save();
            $user = User::findOrFail($request->user_id);
            $currency = Currency::findOrFail($request->currency_id);
            $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
            // $send_data = ['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'reference' => $deposit->deposit_number, 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'shop'=>$shop->name, 'status' => 'pending' ];
            // if($shop->webhook) {
            //     merchant_shop_webhook_send($shop->webhook, $send_data);
            // }
            mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
            send_notification($request->user_id, 'Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no, route('admin.deposits.bank.index'));
            send_whatsapp($request->user_id, 'Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_telegram($request->user_id, 'Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_staff_telegram('Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');


            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => ['reference' => $deposit->deposit_number, 'message' => 'Bank Payment is Pending', 'status' => 'pending']
            ]);
        } else if($request->payment == 'crypto'){
            $data = new CryptoDeposit();
            $data->currency_id = $request->currency_id;
            $data->amount = $request->amount;
            $data->user_id = $request->user_id;
            $data->address = $request->address;
            // $data->proof = '';
            $data->save();
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => ['reference' => Str::random(12), 'message' => 'Crypto Payment is Pending', 'status' => 'pending']
            ]);
        } elseif($request->payment == 'wallet'){
            if(Auth::guest()) {
                return response()->json([
                    'type' => 'login',
                    'payload' => route('api.pay.login')
                ]);
            }
            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 1)->first();

            if(!$wallet){
                $gs = Generalsetting::first();
                $wallet =  Wallet::create([
                    'user_id'     => auth()->id(),
                    'user_type'   => 1,
                    'currency_id' => $request->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail(auth()->id());

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new AppTransaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = 0;
                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      =  $chargefee->data->fixed_charge;
                $trans->type        = '-';
                $trans->remark      = 'account-open';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();
                $currency = Currency::findOrFail(defaultCurr());
                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }
            if($wallet->balance < $request->amount) {
                return response()->json([
                    'type' => 'mt_payment_error',
                    'payload' => 'Insufficient balance to your wallet'
                ]);
            }

            $wallet->balance -= $request->amount;
            $wallet->update();

            $trnx              = new AppTransaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $request->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $request->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'merchant_api_payment';
            $trnx->type        = '-';
            $trnx->details     = trans('Payment to merchant');
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'"}';
            $trnx->save();

            $rcvWallet = MerchantWallet::where('merchant_id', $request->user_id)->where('shop_id', $shop->id)->where('currency_id', $request->currency_id)->first();

            if(!$rcvWallet){
                $gs = Generalsetting::first();
                $rcvWallet =  MerchantWallet::create([
                    'merchant_id'     => $request->user_id,
                    'currency_id' => $request->currency_id,
                    'shop_id' => $shop->id,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

            }

            $rcvWallet->balance += $request->amount;
            $rcvWallet->update();

            $rcvTrnx              = new AppTransaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $request->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $request->currency_id;
            $rcvTrnx->amount      = $request->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'merchant_api_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Merchant Payment');
            $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'"}';
            $rcvTrnx->save();
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => ['reference' => $rcvTrnx->trnx, 'message' => 'Wallet Payment completed', 'status' => 'complete']
            ]);
        }
    }

    public function notify(Request $request, $shop_id, $item_number)
    {

        $paymentId = $request->paymentId;
        $payerId = $request->PayerID;
        $shop = MerchantShop::where('id', $shop_id)->first();
        $merchant_setting = MerchantSetting::where('user_id', $shop->merchant_id)->where('keyword', 'paypal')->first();
        $paydata = $merchant_setting->information;
    
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paydata['client_id'],
                $paydata['client_secret']
            )
        );
    
        $payment = Payment::get($paymentId, $apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);
    
        try {
            $result = $payment->execute($execution, $apiContext);
            // Payment was successful
        } catch (\Exception $e) {
            merchant_shop_webhook_send($shop->webhook, ['type' => 'Paypal', 'shop'=>$shop->name, 'status' => 'reject' ]);
            return back()->with(['msg' => 'Error executing payment with PayPal.']);
        }


        if ($result->getState() == 'approved') {
            $resp = json_decode($payment, true);
            $currency_code = $resp['transactions'][0]['amount']['currency'];
            $amount = $resp['transactions'][0]['amount']['total'];
            $currency = Currency::where('code', $currency_code)->first();
            $rcvWallet = MerchantWallet::where('merchant_id', $merchant_setting->user_id)->where('shop_id', $shop->id)->where('currency_id', $currency->id)->first();
            
            if(!$rcvWallet){
                $gs = Generalsetting::first();
                $rcvWallet =  MerchantWallet::create([
                    'merchant_id'     => $merchant_setting->user_id,
                    'currency_id' => $currency->id,
                    'shop_id' => $shop->id,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

            }

            $rcvWallet->balance += $amount;
            $rcvWallet->update();

            $rcvTrnx  = new AppTransaction();
            $rcvTrnx->trnx = $item_number;
            $rcvTrnx->user_id = $merchant_setting->user_id;
            $rcvTrnx->user_type = 1;
            $rcvTrnx->currency_id = $currency->id;
            $rcvTrnx->amount = $amount;
            $rcvTrnx->charge = 0;
            $rcvTrnx->remark = 'merchant_api_payment';
            $rcvTrnx->type = '+';
            $rcvTrnx->details = trans('Receive Merchant Payment');
            $rcvTrnx->data = '{"sender":"Paypal System", "receiver":"'.(User::findOrFail($merchant_setting->user_id)->company_name ?? User::findOrFail($merchant_setting->user_id)->name).'"}';
            $rcvTrnx->save();

            merchant_shop_webhook_send($shop->webhook, ['amount'=>$amount, 'curr' => $currency->code, 'reference' => $rcvTrnx->txnid, 'date_time'=>$rcvTrnx->created_at ,'type' => 'Paypal', 'shop'=>$shop->name, 'status' => 'complete' ]);

            return redirect()->back()->with('success','Paypal have done successfully!');
        }

    }

    public function cancel(Request $request, $shop_id, $item_number) {
        $shop = MerchantShop::where('id', $shop_id)->first();
        merchant_shop_webhook_send($shop->webhook, ['type' => 'Paypal', 'shop'=>$shop->name, 'status' => 'reject' , 'reference' => $item_number]);

        return redirect()->back()->with('success','Paypal payment for '.$shop->name.' have canceled successfully!');

    }
}
