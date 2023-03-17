<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\MerchantWallet;
use App\Models\PlanDetail;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\MoneyRequest;
use App\Classes\GoogleAuthenticator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;


class MoneyRequestController extends Controller
{

    public function index(){
        try {
            $data['requests'] = MoneyRequest::orderby('id','desc')->whereUserId(auth()->id())->where('user_type', 1)->paginate(10);
            $data['user'] = User::findOrFail(auth()->id());
            $data['receives'] = MoneyRequest::orderby('id','desc')->whereReceiverId(auth()->id())->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create(){
        try {
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['user'] = auth()->user();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request){
        try {
            $user = auth()->user();
            if($user->paymentCheck('Request Money')) {
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
                'account_name' => 'required',
                'wallet_id' => 'required',
                'amount' => 'required|gt:0',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $user = auth()->user();

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }
            $currency = Currency::findOrFail($request->wallet_id);
            $rate = getRate($currency);
            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailyRequests = MoneyRequest::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
            $monthlyRequests = MoneyRequest::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'recieve')->first();


            $gs = Generalsetting::first();
            $receiver = User::where('email',$request->account_email)->first();
            if($request->account_email == $user->email){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!']);
            }


            if($dailyRequests > $global_range->daily_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily request limit over.']);
            }

            if($monthlyRequests > $global_range->monthly_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly request limit over.']);
            }

            if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ]);
            }
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'recieve');
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate * 100)) * $transaction_global_fee->data->percent_charge;
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'recieve');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                }
            }


            $txnid = Str::random(4).time();

            $data = new MoneyRequest();
            $data->user_id = auth()->user()->id;
            $data->receiver_id = $receiver === null ? 0 : $receiver->id;
            $data->receiver_name = $request->account_name;
            $data->receiver_email = $request->account_email;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->wallet_id;
            $data->cost = $transaction_global_cost*$rate;
            $data->supervisor_cost = $user->referral_id != 0 ? $transaction_custom_cost*$rate : 0 ;
            $data->amount = $request->amount;
            $data->status = 0;
            $data->details = $request->details;
            $data->user_type = 1;


            if($receiver === null){
                $gs = Generalsetting::first();
                $to = $request->account_email;
                $subject = " Money Request is receivd";
                $url =     "<button style='height: 50;width: 200px;' ><a href='".route('user.money.request.new', encrypt($txnid))."' target='_blank' type='button' style='color: #2C729E; font-weight: bold; text-decoration: none; '>Confirm</a></button>";

                $msg_body = '
                    <p> Hello ' . $request->account_name . '.</p>
                    <p> You received request money (' . $request->amount . $currency->symbol . ').</p>
                    <p> Please confirm current.</p>
                    ' . $url . '
                    <p> Thank you.</p>
                ';

                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";


                sendMail($to,$subject,$msg_body,$headers);

                $data->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Request Money Send to unregisted user('.$request->account_email.') Successfully.']);

            }
            else {
                $data->save();
                mailSend('request_money_sent',['amount'=>$request->amount, 'curr' => $currency->code, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $data->created_at ], $receiver);
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Request Money Send Successfully.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function send(Request $request, $id){
        try {
            $user = auth()->user();
            if($user->paymentCheck('Receive Request Money')) {
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

            $data = MoneyRequest::findOrFail($id);
            $gs = Generalsetting::first();

            $currency_id = $data->currency_id;
            $sender = User::whereId($data->receiver_id)->first();
            $receiver = User::whereId($data->user_id)->first();

            $currency = Currency::where('id', $currency_id)->first();
            if ($currency->type == 2) {
                if($data->amount > Crypto_Balance($sender->id, $currency_id)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You don\'t have sufficient balance!']);
                }
            }
            else {
                if($data->amount > user_wallet_balance($sender->id, $currency_id)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You don\'t have sufficient balance!']);
                }
            }

            $finalAmount = $data->amount - $data->cost -$data->supervisor_cost;
            $wallet_type = $currency->type == 2 ? 8 : 1;

            user_wallet_decrement($sender->id, $currency_id, $data->amount, $wallet_type);
            user_wallet_increment(0, $currency_id, $data->cost, 9);
            if (isset($data->shop_id)) {
                merchant_shop_wallet_increment($receiver->id, $currency_id, $finalAmount, $data->shop_id);
                $wallet = MerchantWallet::where('merchant_id', $sender->id)->where('currency_id', $currency_id)->where('shop_id', $data->shop_id )->with('currency')->first();
            }
            else {
                user_wallet_increment($receiver->id, $currency_id, $finalAmount, $wallet_type);
                $wallet = Wallet::where('user_id', $sender->id)->where('currency_id', $currency_id)->where('wallet_type', $wallet_type)->first();
            }
            if ($wallet->currency->type == 2) {
                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $towallet = get_wallet(0, $currency_id, 9);
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($data->cost*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    $towallet = get_wallet(0, $currency_id, 9);
                    $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($data->cost, 2)],$wallet->keyword);
                    if (isset($res->error->message)){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                    }
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $towallet = get_wallet(0, $currency_id, 9);
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $data->cost, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                    }

                }
            }
            if ($receiver->referral_id != 0) {
                $remark = 'Reieve_supervisor_fee';
                if($wallet->currency->type == 1) {
                    if (check_user_type_by_id(4, $receiver->referral_id)) {
                        user_wallet_increment($receiver->referral_id, $currency_id, $data->supervisor_cost,6);
                        $trans_wallet = get_wallet($receiver->referral_id, $currency_id,6);
                    }
                    elseif (DB::table('managers')->where('manager_id', $receiver->referral_id)->first()) {
                        $remark = 'Reieve_manager_fee';
                        user_wallet_increment($receiver->referral_id, $currency_id, $data->supervisor_cost,10);
                        $trans_wallet = get_wallet($receiver->referral_id, $currency_id,10);
                    }
                }
                else if ($wallet->currency->type == 2) {
                    $trans_wallet = Wallet::where('user_id', $receiver->referral_id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();;
                    if($wallet->currency->code == 'ETH') {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$trans_wallet->wallet_no.'", "value": "0x'.dechex($data->supervisor_cost*pow(10,18)).'"}';
                        RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                    }
                    else if($wallet->currency->code == 'BTC') {
                        $res = RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, amount($data->supervisor_cost, 2)],$wallet->keyword);
                        if (isset($res->error->message)){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                        }
                    }
                    else {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tokenContract = $wallet->currency->address;
                        $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $data->supervisor_cost, $wallet->keyword);
                        if (json_decode($result)->code == 1){
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                        }
                    }
                }
                $referral_user = User::findOrFail($receiver->referral_id);
                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $receiver->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $data->supervisor_cost;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->remark      = $remark;
                $trans->details     = trans('Request Money');
                $trans->data        = '{"sender":"'.($sender->company_name ?? $sender->name).'", "receiver":"'.($referral_user->company_name ?? $referral_user->name).'", "description": "'.$data->details.'"}';
                $trans->save();
            }

            if ($wallet->currency->type == 2) {
                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($finalAmount*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                    $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($finalAmount, 2)],$wallet->keyword);
                    if (isset($res->error->message)){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('Error: ') . $res->error->message]);
                    }
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $finalAmount, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Ethereum client error: '.json_decode($result)->message]);
                    }
                }
            }
            $data->update(['status'=>1]);

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = auth()->id();
            $trans->user_type   = $data->user_type;
            $trans->currency_id = $currency_id;
            $trans->amount      = $data->amount;

            $trans_wallet       = get_wallet($sender->id, $currency_id);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Recieve';
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$data->details.'"}';
            $trans->details     = trans('Request Money');

            $trans->save();

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = $receiver->id;
            $trans->user_type   = $data->user_type;
            $trans->currency_id = $currency_id;

            $trans_wallet       = get_wallet($receiver->id, $currency_id);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->amount      = $data->amount;
            $trans->charge      = $data->cost + $data->supervisor_cost;
            $trans->type        = '+';
            $trans->remark      = 'Recieve';
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$data->details.'"}';
            $trans->details     = trans('Request Money');

            $trans->save();


            mailSend('request_money_complete',['amount'=>$data->amount, 'curr' => $currency->code, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> $data->cost + $data->supervisor_cost, 'date_time'=> $data->created_at, 'trnx' => $data->transaction_no ], $receiver);
            mailSend('request_money_complete',['amount'=>$data->amount, 'curr' => $currency->code, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $data->created_at, 'trnx' => $data->transaction_no ], $user);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Send.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function cancel($id)
    {
        try {
            $data = MoneyRequest::findOrFail($id);
            $data->update(['status'=>2]);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Request Cancelled.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            $data = MoneyRequest::findOrFail($id);
            $data->delete();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Request Deleted Successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function details($id){
        try {
            $data = MoneyRequest::findOrFail($id);
            $from = User::whereId($data->user_id)->first();
            $to = User::whereId($data->receiver_id)->first();
            $user = User::findOrFail(auth()->id());
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data','from','to', 'user')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
