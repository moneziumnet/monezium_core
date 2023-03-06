<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Models\User;
use App\Models\Wallet;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\CryptoWithdraw;
use App\Models\Generalsetting;
use App\Classes\GoogleAuthenticator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class WithdrawCryptoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['withdraws'] = CryptoWithdraw::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.withdrawcrypto.index',$data);
    }

    public function create(){
        $data['cryptocurrencies'] = Currency::whereType(2)->get();
        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
        $data['user'] = auth()->user();
        return view('user.withdrawcrypto.create',$data);
    }


    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('Withdraw Crypto')) {
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

        $currency = Currency::where('id',$request->currency_id)->first();

        $crypto_rate = getRate($currency);

        $userBalance = Crypto_Balance($request->user_id, $request->currency_id);
        $amountToAdd = $request->amount/$crypto_rate;
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $dailywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }


        if($dailywithdraw/$crypto_rate > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlywithdraw/$crypto_rate > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }

        $transaction_global_cost = 0;

        $transaction_global_fee = check_global_transaction_fee($amountToAdd, $user, 'withdraw_crypto');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amountToAdd/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($amountToAdd, $user, 'withdraw_crypto');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amountToAdd/100) * $transaction_custom_fee->data->percent_charge;
            }
        }

        $messagefee = $transaction_global_cost + $transaction_custom_cost;
        $messagefinal = $amountToAdd - $messagefee;

        if($messagefinal < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')');
        }

        if($request->amount > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }
        user_wallet_decrement($user->id, $currency->id, $request->amount, 8);
        user_wallet_increment(0, $currency->id, $transaction_global_cost*$crypto_rate, 9);
        $fromWallet = Wallet::where('user_id', $user->id)->where('wallet_type', 8)->where('currency_id', $currency->id)->first();
        $toWallet = get_wallet(0,$currency->id,9);
        if($currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$toWallet->wallet_no.'", "value": "0x'.dechex( $transaction_global_cost*$crypto_rate*pow(10,18)).'"}';
            RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
        }
        elseif($currency->code == 'BTC') {
            $res = RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, amount($transaction_global_cost*$crypto_rate, 2)],$fromWallet->keyword);
            if (isset($res->error->message)){
                return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
            }
        }
        else{
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $toWallet->wallet_no, $transaction_global_cost*$crypto_rate,  $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }

        if($user->referral_id != 0) {
            $remark = 'withdraw_crypto_supervisor_fee';
            user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost*$crypto_rate, 8);
            $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $request->currency_id)->first();
            $trans_wallet = get_wallet($user->referral_id, $request->currency_id, 8);
            if (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'withdraw_crypto_manager_fee';
            }
            if($currency->code == 'ETH') {
                @RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$torefWallet->wallet_no.'", "value": "0x'.dechex($transaction_custom_cost*$crypto_rate*pow(10,18)).'"}';
                @RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
            }
            elseif($currency->code == 'BTC') {
                $res = RPC_BTC_Send('sendtoaddress',[$torefWallet->wallet_no, amount($transaction_custom_cost*$crypto_rate, 2)],$fromWallet->keyword);
                if (isset($res->error->message)){
                    return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                }
            }
            else {
                RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                $tokenContract = $fromWallet->currency->address;
                $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $torefWallet->wallet_no, $transaction_custom_cost*$crypto_rate,  $fromWallet->keyword);
                if (json_decode($result)->code == 1){
                    return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                }

            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->amount      = $transaction_custom_cost*$crypto_rate;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Withdraw money');
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'"}';
            $trans->save();
        }
        if($fromWallet->currency->code == 'ETH') {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$request->sender_address.'", "value": "0x'.dechex($messagefinal*$crypto_rate*pow(10,18)).'"}';
            RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
        }
        else if($fromWallet->currency->code == 'BTC') {
            $res = RPC_BTC_Send('sendtoaddress',[$request->sender_address, amount($messagefinal*$crypto_rate, 2)],$fromWallet->keyword);
            if (isset($res->error->message)){
                return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
            }
        }
        else {
            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
            $tokenContract = $fromWallet->currency->address;
            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $request->sender_address, $messagefinal*$crypto_rate,  $fromWallet->keyword);
            if (json_decode($result)->code == 1){
                return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
            }
        }

        $withdraw = new CryptoWithdraw();
        $input = $request->all();
        $withdraw->status = 1;

        $withdraw->fill($input)->save();


        $txnid = Str::random(12);


        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = $messagefee*$crypto_rate;

        $trans_wallet = get_wallet($user->id, $currency->id, 8);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trans->type        = '-';
        $trans->remark      = 'withdraw_crypto';
        $trans->details     = trans('Withdraw money');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$request->sender_address.'"}';
        $trans->save();



        return redirect()->route('user.cryptowithdraw.create')->with('success','Withdraw amount '.$request->amount.' ('.$currency->code.') successfully!');
    }


}
