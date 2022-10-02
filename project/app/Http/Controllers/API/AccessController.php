<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\CryptoDeposit;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AccessController extends Controller
{
    public function index(Request $request) {
        $site_key = $request->site_key ?? Session::get('site_key');
        $amount = 500;
        $currency = Currency::where('code', 'USD')->first();
        $user_api = UserApiCred::where('access_key', $site_key)->first();
        if($user_api) {
            Session::put('site_key', $site_key);
            $user = $user_api->user;
            $bankaccounts = BankAccount::where('user_id', $user->id)->get();
            $cryptolist = Currency::whereStatus(1)->where('type', 2)->get();
            return view('api.payment', compact('bankaccounts','cryptolist','amount','currency','user'));
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
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {

            if(Auth::guard('web')->user()->is_banned == 1) {
              Auth::guard('web')->logout();
              return response()->json(array('errors' => [ 0 => 'You are Banned From this system!' ]));
            }

            if(Auth::guard('web')->user()->email_verified == 'No') {
              Auth::guard('web')->logout();
              return response()->json(array('errors' => [ 0 => 'Your Email is not Verified!' ]));
            }

            return redirect(route('api.pay.index'))->with('message', 'Login successfully.');
        }

        return response()->json(array('errors' => [ 0 => "Credentials Doesn't Match !" ]));
    }

    public function crypto_pay(Request $request) {
        $pre_currency = Currency::findOrFail($request->currency_id)->code;
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $data['total_amount'] = $request->amount;
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$code);
        $result = json_decode($response->getBody());
        $data['cal_amount'] = floatval($result->data->rates->$pre_currency);
        $data['wallet'] =  Wallet::where('user_id', $request->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();

        if(!$data['wallet']) {
            return redirect()->back()->with('error', $select_currency->code .' Crypto wallet does not existed in sender.');
        }
        return view('api.crypto', $data);
    }

    public function pay_submit(Request $request) {
        if($request->payment == 'gateway'){
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Gateway Payment completed'
            ]);
        } else if($request->payment == 'bank_pay'){
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Bank Payment completed'
            ]);
        } else if($request->payment == 'crypto'){
            $data = new CryptoDeposit();
            $data->currency_id = $request->currency_id;
            $data->amount = $request->amount;
            $data->user_id = $request->user_id;
            $data->address = $request->address;
            $data->proof = '';
            $data->save();
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Crypto Payment completed'
            ]);
        } elseif($request->payment == 'wallet'){
            if(Auth::guest()) {
                return redirect(route('api.pay.login'))->with('error', 'You need to login MT Payment System.');
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

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user->id, 1, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }
            if($wallet->balance < $request->amount) {
                return response()->json([
                    'type' => 'mt_payment_error',
                    'payload' => 'Insufficient balance to your wallet'
                ]);
            }

            $wallet->balance -= $request->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $request->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $request->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'merchant_payment';
            $trnx->type        = '-';
            $trnx->details     = trans('Payment to merchant');
            $trnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($request->user_id)->name.'"}';
            $trnx->save();

            $rcvWallet = Wallet::where('user_id',$request->user_id)->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 1)->first();

            if(!$rcvWallet){
                $gs = Generalsetting::first();
                $rcvWallet =  Wallet::create([
                    'user_id'     => $request->user_id,
                    'user_type'   => 1,
                    'currency_id' => $request->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail($request->user_id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $request->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans_wallet = get_wallet($request->user_id, 1, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.User::findOrFail($request->user_id)->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($request->user_id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }

            $rcvWallet->balance += $request->amount;
            $rcvWallet->update();

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $request->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $request->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $request->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'merchant_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Merchant Payment');
            $rcvTrnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($request->user_id)->name.'"}';
            $rcvTrnx->save();
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Wallet Payment completed'
            ]);
        }
    }
}