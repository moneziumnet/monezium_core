<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BalanceTransfer;
use App\Models\BankPlan;
use App\Models\SaveAccount;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\PlanDetail;
use App\Models\Generalsetting;
use Illuminate\Http\Request;

use App\Classes\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;
use DB;

class SendController extends Controller
{
    public $successStatus = 200;

    ////////////////////////////////////Send Money//////////////////////////////////////////
    public function create(){
        try {
            $ga = new GoogleAuthenticator();
            $data['secret'] = $ga->createSecret();
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();
            $data['savedUser'] = NULL;
            $data['user'] = auth()->user();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function store(Request $request){
        try {
            $user = auth()->user();
            if($user->paymentCheck('Internal Payment')) {
                if ($user->payment_fa != 'two_fa_google') {
                    if ($user->two_fa_code != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
                else{
                    $googleAuth = new GoogleAuthenticator();
                    $secret = $user->go;
                    $oneCode = $googleAuth->getCode($secret);
                    if ($oneCode != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
            }

            $rules = [
                'email'    => 'required',
                'wallet_id'         => 'required',
                'account_name'      => 'required',
                'amount'            => 'required|numeric|min:0',
                'description'       => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();

            $currency_id = $wallet->currency->id; //Currency::whereId($wallet_id)->first()->id;
            $rate = getRate($wallet->currency);
            $dailySend = BalanceTransfer::whereType('own')->whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereType('own')->whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();

            if($dailySend > $global_range->daily_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily send limit over.']);
            }

            if($monthlySend > $global_range->monthly_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly send limit over.']);
            }

            $gs = Generalsetting::first();

            if($request->email == $user->email){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!!']);
            }

            if($request->amount < 0){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be greater than 0!']);
            }
            if ($wallet->currency->type == 2) {
                if($request->amount > Crypto_Balance($user->id, $currency_id)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance.' ]);
                }
            }
            else {
                if($request->amount > user_wallet_balance($user->id, $currency_id, $wallet->wallet_type)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance.' ]);
                }
            }

            $transaction_global_cost = 0;
            if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ]);
            }
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'send');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/(100*$rate)) * $transaction_global_fee->data->percent_charge;
            }
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'send');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/(100*$rate)) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'Send_supervisor_fee';
                if ($wallet->currency->type == 1) {
                    if (check_user_type_by_id(4, $user->referral_id)) {
                        user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 6);
                        $trans_wallet = get_wallet($user->referral_id, $currency_id, 6);
                    }
                    elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                        $remark = 'Send_manager_fee';
                        user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 10);
                        $trans_wallet = get_wallet($user->referral_id, $currency_id, 10);
                    }
                }
                else if ($wallet->currency->type == 2) {
                    $trans_wallet = get_wallet($user->referral_id, $currency_id, 8);
                    if($wallet->currency->code == 'ETH') {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$trans_wallet->wallet_no.'", "value": "0x'.dechex($transaction_custom_cost*$rate*pow(10,18)).'"}';
                        RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                    }
                    else if($wallet->currency->code == 'BTC') {
                        $res = RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, amount($transaction_custom_cost*$rate, 2)],$wallet->keyword);
                        if (isset($res->error->message)){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                        }
                    }
                    else if($wallet->currency->code == 'TRON') {
                        $res = RPC_TRON_Transfer($wallet, $trans_wallet->wallet_no, $transaction_custom_cost*$rate);
                        if(!isset($res->txID)) {
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res]);
                        }
                    }
                    else {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tokenContract = $wallet->currency->address;
                        $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $transaction_custom_cost*$rate, $wallet->keyword);
                        if (json_decode($result)->code == 1){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                        }
                    }
                }
                $supervisor_trnx = str_rand();

                $trans = new Transaction();
                $trans->trnx = $supervisor_trnx;
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->currency_id = $currency_id;
                $trans->amount      = $transaction_custom_cost*$rate;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "description": "'.$request->description.'"}';
                $trans->save();

            }

            $finalCharge = $transaction_global_cost+$transaction_custom_cost;
            $finalamount = $request->amount + $finalCharge*$rate;

            if ($wallet->currency->type == 2) {
                $towallet = get_wallet(0, $currency_id, 9);

                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($transaction_global_cost*$rate*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($transaction_global_cost*$rate, 2)],$wallet->keyword);
                    if (isset($res->error->message)){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                    }
                }
                else if($wallet->currency->code == 'TRON') {
                    $res = RPC_TRON_Transfer($wallet, $towallet->wallet_no, $transaction_global_cost*$rate);
                    if(!isset($res->txID)) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res]);
                    }
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $transaction_global_cost*$rate, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                    }
                }
            }
            else {
                user_wallet_increment(0, $currency_id, $transaction_global_cost*$rate, 9);
            }


            if($receiver = User::where('email',$request->email)->first()){

                user_wallet_decrement($user->id, $currency_id, $finalamount, $wallet->wallet_type);
                user_wallet_increment($receiver->id, $currency_id, $request->amount, $wallet->wallet_type);

                if ($wallet->currency->type == 2) {
                    $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                    if($wallet->currency->code == 'ETH') {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($request->amount*pow(10,18)).'"}';
                        RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                    }
                    else if($wallet->currency->code == 'BTC') {
                        $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($request->amount, 2)],$wallet->keyword);
                        if (isset($res->error->message)){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                        }
                    }
                    else if($wallet->currency->code == 'TRON') {
                        $res = RPC_TRON_Transfer($wallet, $towallet->wallet_no, $request->amount);
                        if(!isset($res->txID)) {
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res]);
                        }
                    }
                    else {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tokenContract = $wallet->currency->address;
                        $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, (float)$request->amount, $wallet->keyword);
                        if (json_decode($result)->code == 1){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                        }
                    }
                }

                $txnid = Str::random(4).time();
                $data = new BalanceTransfer();
                $data->user_id = auth()->user()->id;
                $data->receiver_id = $receiver->id;
                $data->transaction_no = $txnid;
                $data->currency_id = $request->wallet_id;
                $data->type = 'own';
                $data->cost = $finalCharge*$rate;
                $data->amount = $finalamount;
                $data->description = $request->description;
                $data->status = 1;
                $data->save();

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans_wallet = get_wallet($user->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->amount      = $request->amount + $finalCharge*$rate;
                $trans->charge      = $finalCharge*$rate;
                $trans->type        = '-';
                $trans->remark      = 'send';
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$request->description.'"}';
                $trans->save();


                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $receiver->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $request->amount;
                $trans_wallet = get_wallet($receiver->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'send';
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$request->description.'"}';
                $trans->save();

                $currency = Currency::findOrFail($currency_id);

                mailSend('send_money',['amount'=>$request->amount, 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $trans->created_at ], $receiver);
                send_notification($receiver->id, $request->amount.$currency->code.' Money is sent from '.($user->company_name ?? $user->name).' to '.($receiver->company_name ?? $receiver->name )."\n Charge Fee : 0".$currency->code."\n Transaction ID : ".$txnid, route('admin-user-transactions', $receiver->id));
                mailSend('send_money',['amount'=>$request->amount + $finalCharge*$rate, 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> $finalCharge*$rate, 'date_time'=> $trans->created_at ], $user);
                send_notification($user->id, ($request->amount + $finalCharge*$rate).$currency->code.' Money is sent from '.($user->company_name ?? $user->name).' to '.($receiver->company_name ?? $receiver->name )."\n Charge Fee : ".$finalCharge*$rate.$currency->code."\n Transaction ID : ".$txnid, route('admin-user-transactions', $user->id));

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You send money successfully.']);
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sender not found!']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

}
