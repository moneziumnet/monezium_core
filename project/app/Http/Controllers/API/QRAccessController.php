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

class QRAccessController extends Controller
{
    public function index(Request $request) {
        $site_key = $request->site_key ?? Session::get('site_key');
        $amount = $request->amount;
        $currencylist = Currency::where('status', 1)->get();
        $user_api = UserApiCred::where('access_key', $site_key)->first();
        if($user_api) {
            Session::put('site_key', $site_key);
            $user = $user_api->user;
            $bankaccounts = BankAccount::where('user_id', $user->id)->get();
            $cryptolist = Currency::whereStatus(1)->where('type', 2)->get();
            return view('merchantqr.payment', compact('bankaccounts','cryptolist','user', 'currencylist'));
        } else {
            return view('merchantqr.error');
        }
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
        return view('merchantqr.crypto', $data);
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
            // $data->proof = '';
            $data->save();
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Crypto Payment completed'
            ]);
        }
    }
}
