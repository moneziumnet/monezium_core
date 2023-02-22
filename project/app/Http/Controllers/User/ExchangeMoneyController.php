<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\ExchangeMoney;
use App\Models\Charge;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\PlanDetail;
use App\Classes\GoogleAuthenticator;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use App\Classes\EthereumRpcService;
use Illuminate\Support\Facades\DB;


class ExchangeMoneyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function exchangeForm()
    {
        $wallets = Wallet::where('user_id', auth()->id())->where('user_type', 1)->get();
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=USD');
        $rate = json_decode($response->getBody());
        $currencies = Currency::where('status', 1)->whereType('1')->get();
        foreach ($currencies as $key => $value) {
            $code = $value->code;
            $currencies[$key]->rate = $rate->data->rates->$code ?? $value->rate;
        }

        $crypto_list = Currency::where('status', 1)->whereType('2')->get();
        $crypto_currencies = [];
        foreach ($crypto_list as $key => $value) {
            $check = Crypto_Net_Check($value->code);
            if ($check != 'error') {
                $code = $value->code;
                $crypto_currencies[$key] = $crypto_list[$key];
                $crypto_currencies[$key]->rate = $rate->data->rates->$code ?? $value->rate;
            }
        }
        $crypto_currencies = json_encode($crypto_currencies);
        $recentExchanges = ExchangeMoney::where('user_id', auth()->id())->with(['fromCurr', 'toCurr'])->latest()->take(7)->get();
        $user = auth()->user();
        return view('user.exchange.exchange', compact('wallets', 'currencies', 'recentExchanges', 'crypto_currencies', 'user', 'rate'));
    }

    public function submitExchange(Request $request)
    {
        $user = auth()->user();
        if ($user->paymentCheck('Exchange')) {
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp_code) {
                    return redirect()->back()->with('unsuccess', 'Verification code is not matched.');
                }
            } else {
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp_code) {
                    return redirect()->back()->with('unsuccess', 'Verification code is not matched.');
                }
            }
        }
        $request->validate([
            'amount' => 'required|gt:0',
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer'
        ], [
                'from_wallet_id.required' => 'From currency is required',
                'to_wallet_id.required' => 'To currency is required',
            ]);


        $fromWallet = Wallet::where('id', $request->from_wallet_id)->where('user_id', auth()->id())->where('user_type', 1)->firstOrFail();

        $toWallet = Wallet::where('currency_id', $request->to_wallet_id)->where('user_id', auth()->id())->where('wallet_type', $request->wallet_type)->where('user_type', 1)->first();
        $currency = Currency::findOrFail($request->to_wallet_id);
        $gs = Generalsetting::first();
        if (!$toWallet) {
            if ($currency->type == 2) {
                if ($currency->code == 'BTC') {
                    $keyword = str_rand();
                    $address = RPC_BTC_Create('createwallet', [$keyword]);
                } else if ($currency->code == 'ETH') {
                    $keyword = str_rand(6);
                    $address = RPC_ETH('personal_newAccount', [$keyword]);
                } else {
                    $eth_currency = Currency::where('code', 'ETH')->first();
                    $eth_wallet = Wallet::where('user_id', $user->id)->where('wallet_type', $request->wallet_type)->where('currency_id', $eth_currency->id)->first();
                    if (!$eth_wallet) {
                        return back()->with('error', 'Now, You do not have Eth Crypto Wallet. You have to create Eth Crypto wallet firstly for this exchange action .');
                    }
                    $address = $eth_wallet->wallet_no;
                    $keyword = $eth_wallet->keyword;
                }
                if ($address == 'error') {
                    return back()->with('error', 'You can not create this wallet because there is some issue in crypto node.');
                }
            } else {
                $address = $gs->wallet_no_prefix . date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            $toWallet = Wallet::create([
                'user_id' => auth()->id(),
                'user_type' => 1,
                'currency_id' => $request->to_wallet_id,
                'balance' => 0,
                'wallet_type' => $request->wallet_type,
                'wallet_no' => $address,
                'keyword' => $keyword
            ]);

            $user = User::findOrFail(auth()->id());
            if ($request->wallet_type == 2) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if (!$chargefee) {
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id = $user->id;
                $trans->user_type = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge = 0;
                $trans->type = '-';
                $trans->remark = 'card_issuance';
                $trans->details = trans('Card Issuance');
                $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                $trans->save();
            } else {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if (!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id = $user->id;
                $trans->user_type = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge = 0;
                $trans->type = '-';
                $trans->remark = 'wallet_create';
                $trans->details = trans('Wallet Create');
                $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                $trans->save();
            }
            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        }
        $user = auth()->user();

        $from_rate = getRate($fromWallet->currency);

        // $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();
        $transaction_global_cost = 0;
        // if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
        //     return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        // }
        if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
            $transaction_global_fee = check_global_transaction_fee($request->amount / $from_rate, $user, 'exchange');
        } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
            $transaction_global_fee = check_global_transaction_fee($request->amount / $from_rate, $user, 'exchange_c_f');
        } else if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 2) {
            $transaction_global_fee = check_global_transaction_fee($request->amount / $from_rate, $user, 'exchange_f_c');
        } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 2) {
            $transaction_global_fee = check_global_transaction_fee($request->amount / $from_rate, $user, 'exchange_c_c');
        }
        if ($transaction_global_fee) {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount / ($from_rate * 100)) * $transaction_global_fee->data->percent_charge;
        }

        $transaction_custom_cost = 0;
        $charge = $transaction_global_cost * $from_rate;
        $totalAmount = $request->amount + $charge;

        if ($fromWallet->currency->type == 2) {
            if ($totalAmount > Crypto_Balance($user->id, $fromWallet->currency->id)) {
                return back()->with('error', 'Insufficient balance to your ' . $fromWallet->currency->code . ' wallet');
            }
        } else {
            if ($totalAmount > $fromWallet->balance) {
                return back()->with('error', 'Insufficient balance to your ' . $fromWallet->currency->code . ' wallet');
            }
        }
        $defaultAmount = $request->amount / $from_rate;
        $finalAmount = $defaultAmount * getRate($toWallet->currency);
        if ($toWallet->currency->type == 2) {
            if ($finalAmount > Crypto_Balance(0, $toWallet->currency->id)) {
                return back()->with('error', 'Insufficient balance to this system ' . $toWallet->currency->code . ' wallet, pleasae contact to admin.');
            }
        }

        if ($user->referral_id != 0) {
            if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount / $from_rate, $user, 'exchange');
            } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount / $from_rate, $user, 'exchange_c_f');
            } else if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 2) {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount / $from_rate, $user, 'exchange_f_c');
            } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 2) {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount / $from_rate, $user, 'exchange_c_c');
            }
            if ($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount / ($from_rate * 100)) * $transaction_custom_fee->data->percent_charge;
            }
            $remark = 'Exchange_money_supervisor_fee';
            if ($fromWallet->currency->type == 1) {

                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 6);
                } elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'Exchange_money_manager_fee';
                    user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 10);
                    $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 10);
                }
            } else {
                user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 8);

                $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 8);
                if ($fromWallet->currency->code == 'ETH') {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $fromWallet->currency_id)->first();

                    RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                    $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $torefWallet->wallet_no . '", "value": "0x' . dechex($transaction_custom_cost * $from_rate * pow(10, 18)) . '"}';
                    RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                } elseif ($fromWallet->currency->code == 'BTC') {
                    $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $fromWallet->currency_id)->first();
                    RPC_BTC_Send('sendtoaddress', [$torefWallet->wallet_no, $transaction_custom_cost * $from_rate], $fromWallet->keyword);
                } else {
                    RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                    $tokenContract = $fromWallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $trans_wallet->wallet_no, $transaction_custom_cost * $from_rate, $fromWallet->keyword);
                    if (json_decode($result)->code == 1){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                    }
                }
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id = $user->referral_id;
            $trans->user_type = 1;

            $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->currency_id = $fromWallet->currency->id;
            $trans->amount = $transaction_custom_cost * $from_rate;
            $trans->charge = 0;
            $trans->type = '+';
            $trans->remark = $remark;
            $trans->details = trans('Exchange Money');
            $trans->data = '{"sender":"' . (auth()->user()->company_name ?? auth()->user()->name) . '", "receiver":"' . (User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name) . '"}';
            $trans->save();
        }

        if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->type == 1) {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet1) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
            $tosystemwallet1->balance -= $finalAmount;
            $tosystemwallet1->update();
        }
        if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->type == 1) {
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet1) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, $totalAmount], $fromWallet->keyword);
            $tosystemwallet1->balance -= $finalAmount;
            $tosystemwallet1->update();
        }
        if ($toWallet->currency->code == 'ETH' && $fromWallet->currency->type == 1) {
            $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$fromsystemwallet1) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            RPC_ETH('personal_unlockAccount', [$tosystemwallet->wallet_no, $tosystemwallet->keyword ?? '', 30]);
            $tx = '{"from": "' . $tosystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $tosystemwallet->keyword ?? '');
            $fromsystemwallet1->balance += $totalAmount;
            $fromsystemwallet1->update();
        }
        if ($toWallet->currency->code == 'BTC' && $fromWallet->currency->type == 1) {
            $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$fromsystemwallet1) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $result = RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, $finalAmount], $tosystemwallet->keyword);
            if (json_decode($result)->code != 0){
                return redirect()->back()->with(array('error' => 'BTC client error: code = '. json_decode($result)->code . ' ' .json_decode($result)->message));
            }

            $fromsystemwallet1->balance += $totalAmount;
            $fromsystemwallet1->update();
        }
        if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
            RPC_ETH('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
        }
        if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code == 'BTC') {
            RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, $totalAmount], $fromWallet->keyword);
        }
        if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code == 'BTC') {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }

            $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
            RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, $finalAmount], $fromsystemwallet->keyword);
        }
        if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code == 'ETH') {
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, $totalAmount], $fromWallet->keyword);
            RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
            $tx = '{"from": "' . $fromsystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $fromsystemwallet->keyword ?? '');
        }
        if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->code != 'ETH' && $toWallet->currency->type == 2) {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');

            RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
            $tokenContract = $toWallet->currency->address;
            $result = erc20_token_transfer($tokenContract,  $fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }
        if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, $totalAmount], $fromWallet->keyword);

            RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
            $tokenContract = $toWallet->currency->address;
            $result = erc20_token_transfer($tokenContract,  $fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }

        if ($fromWallet->currency->type == 1 && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
            $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$fromsystemwallet1) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            RPC_ETH('personal_unlockAccount', [$tosystemwallet->wallet_no, $tosystemwallet->keyword ?? '', 30]);

            $tokenContract = $toWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $tosystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $tosystemwallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }

            $fromsystemwallet1->balance += $totalAmount;
            $fromsystemwallet1->update();
        }

        if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code == 'BTC') {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
            
            RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, $finalAmount], $fromsystemwallet->keyword);
        }
        if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }

            RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
            $tx = '{"from": "' . $fromsystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
            RPC_ETH_Send('personal_sendTransaction', $tx, $fromsystemwallet->keyword ?? '');
        }

        if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
            if (!$tosystemwallet1) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }

            $tosystemwallet1->balance -= $finalAmount;
            $tosystemwallet1->update();
        }

        if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
            RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
            if (!$tosystemwallet) {
                return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
            if (!$fromsystemwallet) {
                return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
            }
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }

            RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
            $tokenContract = $toWallet->currency->address;
            $result = erc20_token_transfer($tokenContract,$fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }

        if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
            user_wallet_increment(0, $fromWallet->currency->id, $transaction_global_cost * $from_rate, 9);
        }

        $fromWallet->balance -= $totalAmount;

        $fromWallet->update();

        $toWallet->balance += $finalAmount;
        $toWallet->update();

        $exchange = new ExchangeMoney();
        $exchange->trnx = str_rand();
        $exchange->from_currency = $fromWallet->currency->id;
        $exchange->to_currency = $toWallet->currency->id;
        $exchange->user_id = auth()->id();
        $exchange->charge = $charge + $transaction_custom_cost * $from_rate;
        $exchange->from_amount = $request->amount;
        $exchange->to_amount = $finalAmount;
        $exchange->save();

        @mailSend('exchange_money', ['from_curr' => $fromWallet->currency->code, 'to_curr' => $toWallet->currency->code, 'charge' => amount($charge + $transaction_custom_cost * $from_rate, $fromWallet->currency->type, 3), 'from_amount' => amount($request->amount, $fromWallet->currency->type, 3), 'to_amount' => amount($finalAmount, $toWallet->currency->type, 3), 'date_time' => dateFormat($exchange->created_at)], auth()->user());

        return back()->with('message', 'Money exchanged successfully.');
    }

    public function calcharge($amount, $fromtype, $totype)
    {
        $user = auth()->user();
        // $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();
        $transaction_global_cost = 0;
        // if ($amount < $global_range->min || $amount > $global_range->max) {
        //     return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        // }
        if ($fromtype == 1 && $totype == 1) {
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'exchange');
        } else if ($fromtype == 2 && $totype == 1) {
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'exchange_c_f');
        } else if ($fromtype == 1 && $totype == 2) {
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'exchange_f_c');
        } else if ($fromtype == 2 && $totype == 2) {
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'exchange_c_c');
        }
        if ($transaction_global_fee) {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount / 100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if ($user->referral_id != 0) {
            $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'exchange');
            if ($fromtype == 1 && $totype == 1) {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'exchange');
            } else if ($fromtype == 2 && $totype == 1) {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'exchange_c_f');
            } else if ($fromtype == 1 && $totype == 2) {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'exchange_f_c');
            } else if ($fromtype == 2 && $totype == 2) {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'exchange_c_c');
            }
            if ($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount / 100) * $transaction_custom_fee->data->percent_charge;
            }
        }
        // if(check_user_type(3))
        // {
        //     $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'send');
        //     if($transaction_custom_fee) {
        //         $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
        //     }
        // }
        $finalCharge = $transaction_global_cost + $transaction_custom_cost;
        return $finalCharge;
    }

    public function exchangeHistory()
    {
        $search = request('search');
        $exchanges = ExchangeMoney::where('user_id', auth()->id())->when($search, function ($q) use ($search) {
            return $q->where('trnx', $search);
        })->with(['fromCurr', 'toCurr'])->latest()->paginate(15);
        return view('user.exchange.history', compact('exchanges', 'search'));
    }
}