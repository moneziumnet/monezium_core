<?php

namespace App\Http\Controllers\Admin;

use Datatables;

use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\CryptoApi;
use App\Models\Transaction;
use App\Models\SubInsBank;
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
        $data['banks'] = SubInsBank::where('status', 1)->get();

        return view('admin.system.systemwallet',$data);
    }

    public function transactions($id) {
        $wallet = Currency::findOrFail($id);
        $data['data'] = $wallet;
        $remark_list = Transaction::where('currency_id', $id)->where('charge','>', 0)->orWhere('user_id', 0)->where('currency_id', $id)->orderBy('remark', 'asc')->pluck('remark')->map(function ($item) {
            return ucfirst($item);
        });
        $data['remark_list'] = array_unique($remark_list->all());

        $month_list =  Transaction::where('currency_id', $id)->where('charge','>', 0)->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as created_date")->orWhere('user_id', 0)->where('currency_id', $id)->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as created_date")->get()->pluck('created_date')->toArray();
        $data['month_list'] = array_unique($month_list);
        return view('admin.system.transactions',$data);

    }

    public function trn_datables(Request $request, $id) {
        $datas = Transaction::where('currency_id', $id)->where('charge','>', 0)->orWhere('user_id', 0)->where('currency_id', $id)->orderBy('created_at','desc')->get();

        return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {

            if (!empty($request->get('sender'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    return Str::contains(Str::lower($row['sender']), Str::lower($request->get('sender'))) ? true : false;
                });
            }
            if (!empty($request->get('receiver'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    return Str::contains(Str::lower($row['receiver']), Str::lower($request->get('receiver'))) ? true : false;
                });
            }
            if (!empty($request->get('trnx_no'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    return Str::contains(Str::lower($row['trnx']), Str::lower($request->get('trnx_no'))) ? true : false;
                });
            }
            if (!empty($request->get('remark'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    return Str::contains(Str::lower($row['remark']), Str::lower($request->get('remark'))) ? true : false;
                });
            }
            if (!empty($request->get('s_time'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    if(dateFormat($row['created_at'], 'Y-m-d') >= dateFormat($request->get('s_time'), 'Y-m-d') && dateFormat($row['created_at'], 'Y-m-d') <= (dateFormat($request->get('e_time'), 'Y-m-d') ?? Carbontime::now()->addDays(1)->format('Y-m-d'))) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
            }

            if (!empty($request->get('e_time'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    if(dateFormat($row['created_at'], 'Y-m-d') <= dateFormat($request->get('e_time'), 'Y-m-d') ) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
            }

            if (!empty($request->get('month'))) {
                $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                    if(dateFormat($row['created_at'], 'Y-m') == dateFormat($request->get('month'), 'Y-m') ) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
            }
        })
        ->editColumn('amount', function(Transaction $data) {
            $currency = Currency::whereId($data->currency_id)->first();
            return $data->type.amount($data->amount,$currency->type,2).$currency->code;
        })
        ->editColumn('trnx', function(Transaction $data) {
            $trnx = $data->trnx;
            return $trnx;
        })
        ->editColumn('sender', function(Transaction $data) {
            $details = json_decode(str_replace(array("\r", "\n"), array('\r', '\n'), $data->data));
            return ucwords($details->sender ?? "");
        })
        ->editColumn('receiver', function(Transaction $data) {
            $details = json_decode(str_replace(array("\r", "\n"), array('\r', '\n'), $data->data));
            return str_dis(ucwords($details->receiver ?? ""));
        })
        ->editColumn('created_at', function(Transaction $data) {
            $date = date('Y-M-d',strtotime($data->created_at));
            return $date.'<br>'.$data->trnx;
        })
        ->editColumn('remark', function(Transaction $data) {
            return ucwords(str_replace('_',' ',$data->remark));
        })
        ->editColumn('charge', function(Transaction $data) {
            $currency = Currency::whereId($data->currency_id)->first();
            return '-'.amount($data->charge,$currency->type,2).$currency->code;
        })
        ->addColumn('action', function (Transaction $data) {
            return ' <a href="javascript:;"  data-href="" onclick="getDetails('.$data->id.')" class="detailsBtn" >
            ' . __("Details") . '</a>';
        })

        ->rawColumns(['created_at','action'])
        ->toJson();
    }

    public function crypto_withdraw_form($id) {
        $data['user_id'] = 0;
        $data['currency_id'] = $id;
        return view('admin.system.cryptowithdraw', $data);
    }

    public function crypto_withdraw_store(Request $request) {


        $currency = Currency::where('id',$request->currency_id)->first();

        $fromWallet = Wallet::where('user_id',$request->user_id)->where('wallet_type', 9)->where('currency_id', $request->currency_id)->first();

        $userBalance = Crypto_Balance($request->user_id, $request->currency_id);
        if($request->amount > $userBalance){
            return redirect()->back()->with('error','Insufficient Account Balance.');
        }

        if($fromWallet->currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$request->receiver_address.'", "value": "0x'.dechex($request->amount*pow(10,18)).'"}';
            RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
        }
        else if($fromWallet->currency->code == 'BTC') {
            $res = RPC_BTC_Send('sendtoaddress',[$request->receiver_address, amount($request->amount, 2)],$fromWallet->keyword);
            if (isset($res->error->message)){
                return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
            }
        }
        else if ($fromWallet->currency->code == 'TRON') {
            $from = new \Tron\Address($fromWallet->wallet_no, $fromWallet->keyword );
            $to = new \Tron\Address($request->receiver_address);
            $res = RPC_TRON_Transfer($from, $to, $request->amount);
            if(!isset($res->txID)) {
                return redirect()->back()->with(array('error' => __('Error: ') . $res));
            }

        }
        else {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $request->receiver_address, $request->amount,  $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }

        $txnid = Str::random(12);
        $gs = Generalsetting::first();


        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = 0;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = 0;

        $trans_wallet = get_wallet($request->user_id, $currency->id, 9);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trans->type        = '-';
        $trans->remark      = 'withdraw_crypto';
        $trans->details     = trans('Withdraw money');
        $trans->data        = '{"sender":"'.($gs->disqus).'", "receiver":"'.$request->receiver_address.'"}';
        $trans->save();

        return redirect()->back()->with('message','Cryto Withdraw successfully.');

    }

    public function withdraw_store(Request $request) {
        $wallet = Wallet::where('user_id',0)->where('wallet_type', 9)->where('currency_id', $request->currency_id)->first();
        user_wallet_decrement(0, $request->currency_id, $request->amount, 9);

        $txnid = Str::random(12);
        $gs = Generalsetting::first();


        $trax_details = $request->except('_token', 'currency_id', 'beneficiary_user_name');
        $trax_details['sender'] = $gs->disqus;
        $trax_details['receiver'] = $request->beneficiary_user_name;
        $trax_details = json_encode($trax_details, True);

        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = 0;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = 0;

        $trans->wallet_id   = $wallet->id;

        $trans->type        = '-';
        $trans->remark      = 'withdraw';
        $trans->details     = trans('Withdraw money from System Wallet');
        $trans->data        = $trax_details;
        $trans->save();

        return redirect()->back()->with('message','Withdraw successfully.');
    }

    public function create($currency_id)
    {
            $wallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $currency_id)->first();
            $currency =  Currency::findOrFail($currency_id);
            $gs = Generalsetting::first();
            if(!$wallet)
            {
                if ($currency->type == 2) {
                    if ($currency->code == 'BTC') {
                        $keyword = str_rand();
                        $address = RPC_BTC_Create('createwallet',[$keyword]);
                    }
                    elseif ($currency->code == 'ETH'){
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount',[$keyword]);
                    }
                    elseif ($currency->code == 'TRON') {
                        $addressData = RPC_TRON_Create();
                        $address = $addressData->address;
                        $keyword = $addressData->privateKey;
                    }
                    elseif ($currency->code == 'USDT(TRON)' && $currency->curr_name == 'Tether USD TRC20') {
                        $tron_currency = Currency::where('code', 'TRON')->first();
                        $tron_wallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $tron_currency->id)->first();
                        if (!$tron_wallet) {
                            response()->json(array('errors' => [0 => __('You have to create TRON Crypto wallet firstly before create TRC20 token wallet.')]));
                        }
                        $address = $tron_wallet->wallet_no;
                        $keyword = $tron_wallet->keyword;
                    }
                    else {
                        $eth_currency = Currency::where('code', 'ETH')->first();
                        $eth_wallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $eth_currency->id)->first();
                        if (!$eth_wallet) {
                            response()->json(array('errors' => [0 => __('You have to create Eth Crypto wallet firstly before create ERC20 token wallet.')]));
                        }
                        $address = $eth_wallet->wallet_no;
                        $keyword = $eth_wallet->keyword;
                    }
                }
                else {
                    $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                    $keyword = '';
                }
                if (!isset($address) || $address == 'error') {
                    return response()->json(array('errors' => [0 => __('You can not create this wallet because there is some issue in crypto node.')]));
                }
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
        $data['balance'] = "";
        if($data['api'] && $data['api']->api_key && $data['api']->api_secret)
        {
            $beta = false;
            $url = $beta ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
            $sslverify = $beta ? false : true;
            $version = 0;
            $key = $data['api']->api_key;
            $secret = $data['api']->api_secret;
            $kraken = new KrakenAPI($key, $secret, $url, $version, $sslverify);
            $res = $kraken->QueryPrivate('Balance');
            if(count($res['error']) > 0) {
                $data['balance'] = 0;
                // return redirect()->back()->with(array('warning' => json_encode((object)$res['error']) ));
            }
            else {
                $data['balance'] =(object)$res['result'];
            }
        }
        $data['keyword'] = $keyword;
        return view('admin.system.cryptosettings', $data);
    }

    public function binance_setting()
    {
        $data['api'] = CryptoApi::where('keyword', 'binance')->first();
        if($data['api']) {

            if($data['api']->api_key && $data['api']->api_secret) {
                $key = $data['api']->api_key;
                $secret = $data['api']->api_secret;
                $binance = new BinanceAPI($key, $secret);
                $tickers = $binance->prices();
                $balances = $binance->balances($tickers);
                $data['balance'] =(object)$balances;
                // dd($data['balance']);
            }
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
