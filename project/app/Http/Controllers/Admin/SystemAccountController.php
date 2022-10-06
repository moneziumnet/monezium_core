<?php

namespace App\Http\Controllers\Admin;

use Datatables;

use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\CryptoApi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\AdminExportTransaction;
use App\Classes\KrakenAPI;
use App\Classes\BinanceAPI;


class SystemAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function systemAccounts()
    {
        $wallets = Wallet::where('user_id',0)->where('wallet_type', 9)->with('currency')->get();
        $data['wallets'] = $wallets;
        return view('admin.system.systemwallet',$data);
    }

    public function create($currency_id)
    {
            $wallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $currency_id)->first();
            $currency =  Currency::findOrFail($currency_id);
            $gs = Generalsetting::first();
            if ($currency->type == 2) {
                if ($currency->code == 'BTC') {
                    $keyword = str_rand();
                    $address = RPC_BTC_Create('createwallet',[$keyword]);
                }
                elseif ($currency->code == 'ETH'){
                    $address = RPC_ETH('personal_newAccount',['123123']);
                    $keyword = '123123';
                }
            }
            else {
                $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            if (!isset($address) || $address == 'error') {
                return response()->json(array('errors' => [0 => __('You can not create this wallet because there is some issue in crypto node.')]));
            }
            if(!$wallet)
            {
                $user_wallet = new Wallet();
                $user_wallet->user_id = 0;
                $user_wallet->user_type = 1;
                $user_wallet->currency_id = $currency_id;
                $user_wallet->balance = 0;
                $user_wallet->wallet_type = 9;
                $user_wallet->wallet_no =$address;
                $user_wallet->keyword = $keyword;
                $user_wallet->created_at = date('Y-m-d H:i:s');
                $user_wallet->updated_at = date('Y-m-d H:i:s');
                $user_wallet->save();

                $msg = __('Account New Wallet Updated Successfully.');
                return response()->json($msg);
            }
            else {
                return response()->json(array('errors' => [0 =>'This wallet has already been created.']));
            }

    }

    public function setting($keyword)
    {
        $data['api'] = CryptoApi::where('keyword', $keyword)->first();
        if($data['api'])
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);
            $res = $kraken->QueryPrivate('Balance');
            $data['balance'] =(object)$res['result'];
            if(count($res['error']) > 0) {
                return redirect()->back()->with(array('warning' => json_encode((object)$res['error']) ));
            }
        }
        $data['keyword'] = $keyword;
        return view('admin.system.cryptosettings', $data);
    }

    public function binance_setting()
    {
        $data['api'] = CryptoApi::where('keyword', 'binance')->first();
        if($data['api']) {
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $binance = new BinanceAPI($key, $secret);
            $tickers = $binance->prices();
            $balances = $binance->balances($tickers);
            $data['balance'] =(object)$balances;
            // dd($data['balance']);
        }
        $data['keyword'] = 'binance';
        return view('admin.system.cryptobinancesettings', $data);
    }

    public function setting_save(Request $request)
    {
        $data = CryptoApi::where('keyword', $request->keyword)->first();
        if(!$data) {
            $data = new CryptoApi();
        }
        $input = $request->all();
        $data->fill($input)->save();
        $msg = __('Crypto Api Key Updated Successfully.');
        return response()->json($msg);
    }

    public function order(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);

            $res = $kraken->QueryPrivate('AddOrder', array(
                'pair' => $request->pair_type,
                'type' => $request->order_type,
                'ordertype' => 'market',
                'volume' => $request->amount,
            ));
            if(count($res['error']) > 0) {
                return redirect()->back()->with(array('warning' => json_encode((object)$res['error']) ));
            }
        }
        $msg = __('Crypto Exchange Successfully.');
        return redirect()->back()->with(array('message' => $msg));
    }

    public function binance_order(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $binance = new BinanceAPI($key, $secret);

            $res = $binance->order($request->order_type,
                $request->pair_type,
                $request->amount,
                'MARKET',
            );
            if(isset($res['code'])) {
                return redirect()->back()->with(array('warning' => json_encode((object)$res['msg']) ));
            }
        }
        $msg = __('Crypto Exchange Successfully.');
        return redirect()->back()->with(array('message' => $msg));
    }

    public function withdraw(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);
            $res = $kraken->QueryPrivate('Withdraw', array(
                'asset' => $request->asset,
                'key' => $request->withdraw_key,
                'volume' => $request->amount,
            ));
            if(count($res['error']) > 0) {
                return redirect()->back()->with(array('warning' => json_encode((object)$res['error']) ));
            }
        }
        $msg = __('Crypto Withdraw Successfully.');
        return redirect()->back()->with(array('message' => $msg));
    }

    public function binance_withdraw(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $binance = new BinanceAPI($key, $secret);
            $res = $binance->withdraw(
                $request->asset,
                $request->address,
                $request->amount
                );
                if(isset($res['code'])) {
                    return redirect()->back()->with(array('warning' => json_encode((object)$res['msg']) ));
                }
        }
        $msg = __('Crypto Withdraw Successfully.');
        return redirect()->back()->with(array('message' => $msg));
    }

    public function depositMethods(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);
            $res = $kraken->QueryPrivate('DepositMethods', array(
                'asset' => $request->asset,
            ));
            $data['result'] =(object)$res['result'];
        }
        return $data['result'];
    }

    public function depositAddresses(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);

            $res = $kraken->QueryPrivate('DepositAddresses', array(
                'asset' => $request->asset,
                'method' => $request->method
            ));
            $data['result'] =(object)$res['result'];
        }
        return $data['result'];
    }

    public function binance_depositAddresses(Request $request)
    {
        $data['api'] = CryptoApi::where('keyword', $request->keyword)->first();
        if($data['api'])
        {
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $binance = new BinanceAPI($key, $secret);
            $data['result'] = $binance->depositAddress($request->asset);

        }
        return $data['result'];
    }

}
