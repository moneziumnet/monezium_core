<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\MerchantWallet;
use App\Models\PlanDetail;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Classes\GoogleAuthenticator;
use App\Models\MoneyRequest;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use App\Classes\EthereumRpcService;

class MoneyRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'request_user']);
    }

    public function index(){
        $data['requests'] = MoneyRequest::orderby('id','desc')->whereUserId(auth()->id())->where('user_type', 1)->paginate(10);
        $data['user'] = User::findOrFail(auth()->id());
        // if(auth()->user()->twofa)
        // {
            $data['receives'] = MoneyRequest::orderby('id','desc')->whereReceiverId(auth()->id())->paginate(10);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }
        return view('user.requestmoney.index',$data);
    }

    public function create(){
        $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
        $data['wallets'] = $wallets;
        $data['user'] = auth()->user();
        return view('user.requestmoney.create', $data);
    }

    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('Request Money')) {
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
        $request->validate([
            'account_name' => 'required',
            'wallet_id' => 'required',
            'amount' => 'required|gt:0',
        ]);

        $user = auth()->user();

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }
        $currency = Currency::findOrFail($request->wallet_id);
        $rate = getRate($currency);
        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $dailyRequests = MoneyRequest::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
        $monthlyRequests = MoneyRequest::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'recieve')->first();


        $gs = Generalsetting::first();
        $receiver = User::where('email',$request->account_email)->first();
        if($request->account_email == $user->email){
            return redirect()->back()->with('unsuccess','You can not send money yourself!');
        }


        if($dailyRequests > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily request limit over.');
        }

        if($monthlyRequests > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly request limit over.');
        }

        if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'recieve');
        $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate * 100)) * $transaction_global_fee->data->percent_charge;
        if($user->referral_id != 0)
        {
            $transaction_custom_cost = 0;
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
            $subject = " Money Request";
            $url =     "<button style='height: 50;width: 200px;' ><a href='".route('user.money.request.new', encrypt($txnid))."' target='_blank' type='button' style='color: #2C729E; font-weight: bold; text-decoration: none; '>Confirm</a></button>";
            // $msg = "Hello ".$request->account_name."!\nYou received request money (".$request->amount.$currency->symbol.").\nPlease confirm current.\n".$url."\n Thank you.";

            $msg_body = '
            <!DOCTYPE html>
            <html lang="en-US">
                <head>
                    <meta charset="utf-8"><title>Request Money</title>
                </head>
                <body>
                    <p> Hello '.$request->account_name.'.</p>
                    <p> You received request money ('.$request->amount.$currency->symbol.').</p>
                    <p> Please confirm current.</p>
                    '.$url.'
                    <p> Thank you.</p>

                </body>
            </html>
            ';

            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers

            @sendMail($to,$subject,$msg_body,$headers);
            $data->save();
            return redirect(route('user.money.request.index'))->with('message','Request Money Send to unregisted user('.$request->account_email.') Successfully.');

        }
        else {
            $data->save();
            return redirect(route('user.money.request.index'))->with('message','Request Money Send Successfully.');
        }

    }

    public function send(Request $request, $id){
        $user = auth()->user();
        if($user->paymentCheck('Receive Request Money')) {
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

        $data = MoneyRequest::findOrFail($id);
        $gs = Generalsetting::first();

        $currency_id = $data->currency_id;
        $sender = User::whereId($data->receiver_id)->first();
        $receiver = User::whereId($data->user_id)->first();

        $currency = Currency::where('id', $currency_id)->first();
        if ($currency->type == 2) {
            if($data->amount > Crypto_Balance($sender->id, $currency_id)){
                return back()->with('error','You don\'t have sufficient balance!');
            }
        }
        else {
            if($data->amount > user_wallet_balance($sender->id, $currency_id)){
                return back()->with('error','You don\'t have sufficient balance!');
            }
        }

        $finalAmount = $data->amount - $data->cost -$data->supervisor_cost;
        $wallet_type = $currency->type == 2 ? 8 : 1;

        // user_wallet_decrement($sender->id, $currency_id, $data->amount, $wallet_type);
        // user_wallet_increment(0, $currency_id, $data->cost, 9);
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
                RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, $data->cost],$wallet->keyword);
            }
            else {
                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                $towallet = get_wallet(0, $currency_id, 9);
                $tokenContract = $wallet->currency->address;
                $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $data->cost, $wallet->keyword);
                if (json_decode($result)->code == 1){
                    return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                }

            }
        }
        if ($receiver->referral_id != 0) {
            $remark = 'Request_money_supervisor_fee';
            if($wallet->currency->type == 1) {
                if (check_user_type_by_id(4, $receiver->referral_id)) {
                    user_wallet_increment($receiver->referral_id, $currency_id, $data->supervisor_cost,6);
                    $trans_wallet = get_wallet($receiver->referral_id, $currency_id,6);
                }
                elseif (DB::table('managers')->where('manager_id', $receiver->referral_id)->first()) {
                    $remark = 'Request_money_manager_fee';
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
                    RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, $data->supervisor_cost],$wallet->keyword);
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $data->supervisor_cost, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
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
                RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, $finalAmount],$wallet->keyword);
            }
            else {
                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                $tokenContract = $wallet->currency->address;
                $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $finalAmount, $wallet->keyword);
                if (json_decode($result)->code == 1){
                    return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
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
        $trans->remark      = 'Request_Money';
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
        $trans->remark      = 'Request_Money';
        $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$data->details.'"}';
        $trans->details     = trans('Request Money');

        $trans->save();


            $to = $receiver->email;
            $subject = " Money send successfully.";
            $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            sendMail($to,$subject,$msg,$headers);
        return redirect()->route('user.money.request.index')->with('message','Successfully Money Send.');
        //return back()->with('message','Successfully Money Send.');
    }

    public function cancel($id)
    {
        $data = MoneyRequest::findOrFail($id);
        $data->update(['status'=>2]);
        return back()->with('message','Successfully Money Request Cancelled.');
    }

    public function delete($id)
    {
        $data = MoneyRequest::findOrFail($id);
        $data->delete();
        return back()->with('message','Money Request Deleted Successfully.');
    }

    public function request_user($id) {
        $data = MoneyRequest::where('transaction_no', decrypt($id))->first();
        if(auth()->user()) {

            if($data) {
                $data->receiver_id = auth()->id();
                $data->update();
            }
            return redirect()->route('user.money.request.index');
        }
        else {
            session()->put('setredirectroute', URL::current());
            return redirect()->route('user.register',1);
        }
    }

    public function details($id){
        $data = MoneyRequest::findOrFail($id);
        $from = User::whereId($data->user_id)->first();
        $to = User::whereId($data->receiver_id)->first();
        $user = User::findOrFail(auth()->id());
        return view('user.requestmoney.details',compact('data','from','to', 'user'));
    }
}
