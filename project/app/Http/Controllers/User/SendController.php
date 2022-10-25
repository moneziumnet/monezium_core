<?php

namespace App\Http\Controllers\User;

use Validator;
use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\SaveAccount;
use App\Models\Transaction;
use App\Models\Charge;
use App\Models\PlanDetail;
use Illuminate\Support\Str;
use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use App\Classes\EthereumRpcService;

class SendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(){

        // if(auth()->user()->twofa)
        // {
            $ga = new GoogleAuthenticator();
            $data['secret'] = $ga->createSecret();
            $wallets = Wallet::where('user_id',auth()->id())->where('balance','>',0)->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();
            $data['savedUser'] = NULL;
            $data['user'] = auth()->user();

            return view('user.sendmoney.create',$data);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }

    }

    public function savedUser($no){
        // if(auth()->user()->twofa)
        // {
            $ga = new GoogleAuthenticator();
            $data['secret'] = $ga->createSecret();
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['savedUser'] = User::whereEmail($no)->first();
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();
            $data['user'] = auth()->user();

            return view('user.sendmoney.create',$data);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }
    }

    public function success(){
        if(session('saveData') && session('sendstatus') == 1){
            $data['data'] = session()->get('saveData');
            $data['user_id'] = auth()->user()->id;

            session(['sendstatus'=>0]);
            return view('user.sendmoney.success',$data);
        }else{
            session(['sendstatus'=>0]);
            $data['savedUser'] =  NULL;
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

            return view('user.sendmoney.create',$data);
        }
    }


    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('Internal Payment')) {
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $request->validate([
            'email'    => 'required',
            'wallet_id'         => 'required',
            'account_name'      => 'required',
            'amount'            => 'required|numeric|min:0',
            'description'       => 'required',
        ]);

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }
        $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();

        $currency_id = $wallet->currency->id; //Currency::whereId($wallet_id)->first()->id;
        $rate = getRate($wallet->currency);
        $dailySend = BalanceTransfer::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
        $monthlySend = BalanceTransfer::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();

        if($dailySend > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily send limit over.');
        }

        if($monthlySend > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly send limit over.');
        }

        $gs = Generalsetting::first();

        if($request->email == $user->email){
            return redirect()->back()->with('unsuccess','You can not send money yourself!!');
        }

        if($request->amount < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than 0!');
        }
        if ($wallet->currency->type == 2) {
            if($request->amount > Crypto_Balance($user->id, $currency_id)){
                return redirect()->back()->with('unsuccess','Insufficient Balance.');
            }
        }
        else {
            if($request->amount > user_wallet_balance($user->id, $currency_id, $wallet->wallet_type)){
                return redirect()->back()->with('unsuccess','Insufficient Balance.');
            }
        }

        $transaction_global_cost = 0;
        if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
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
            $remark = 'Send_money_supervisor_fee';
            if ($wallet->currency->type == 1) {
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $currency_id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'Send_money_manager_fee';
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
                    RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, $transaction_custom_cost*$rate],$wallet->keyword);
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $geth = new EthereumRpcService();
                    $tokenContract = $wallet->currency->address;
                    $result = $geth->transferToken($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $transaction_custom_cost*$rate);
                    if (isset($result->error)){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.$result->error->message));
                    }
                }
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->currency_id = $currency_id;
            $trans->amount      = $transaction_custom_cost*$rate;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Send Money');
            $trans->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($user->referral_id)->name.'"}';
            $trans->save();

        }

        $finalCharge = $transaction_global_cost+$transaction_custom_cost;
        $finalamount = $request->amount - $finalCharge*$rate;

        if ($wallet->currency->type == 2) {
            $towallet = get_wallet(0, $currency_id, 9);

            if($wallet->currency->code == 'ETH') {
                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($transaction_global_cost*$rate*pow(10,18)).'"}';
                RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
            }
            else if($wallet->currency->code == 'BTC') {
                RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, $transaction_global_cost*$rate],$wallet->keyword);
            }
            else {
                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                $geth = new EthereumRpcService();
                $tokenContract = $wallet->currency->address;
                $result = $geth->transferToken($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $transaction_global_cost*$rate);
                if (isset($result->error)){
                    return redirect()->back()->with(array('error' => 'Ethereum client error: '.$result->error->message));
                }
            }
        }

        user_wallet_increment(0, $currency_id, $transaction_global_cost*$rate, 9);

        if($receiver = User::where('email',$request->email)->first()){

            user_wallet_decrement($user->id, $currency_id, $request->amount, $wallet->wallet_type);
            user_wallet_increment($receiver->id, $currency_id, $finalamount, $wallet->wallet_type);

            if ($wallet->currency->type == 2) {
                $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($finalamount*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, $finalamount],$wallet->keyword);
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $geth = new EthereumRpcService();
                    $tokenContract = $wallet->currency->address;
                    $result = $geth->transferToken($tokenContract, $wallet->wallet_no, $towallet->wallet_no, (float)$finalamount);
                    if (isset($result->error)){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.$result->error->message));
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
            $data->amount = $request->amount;
            $data->description = $request->description;
            $data->status = 1;
            $data->save();

            // $receiver->increment('balance',$request->amount);
            // $user->decrement('balance',$request->amount);


            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency_id;
            $trans_wallet = get_wallet($user->id, $currency_id, $wallet->wallet_type);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->amount      = $request->amount;
            $trans->charge      = $finalCharge;
            $trans->type        = '-';
            $trans->remark      = 'Internal Payment';
            $trans->details     = trans('Send Money');
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'"}';
            $trans->save();


            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $receiver->id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency_id;
            $trans->amount      = $finalamount;
            $trans_wallet = get_wallet($receiver->id, $currency_id, $wallet->wallet_type);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Internal Payment';
            $trans->details     = trans('Send Money');
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'"}';
            $trans->save();

            session(['sendstatus'=>1, 'saveData'=>$trans]);
            // user_wallet_decrement($user->id, $currency_id, $request->amount);
            // user_wallet_increment($receiver->id, $currency_id, $request->amount);

            if(SaveAccount::whereUserId(auth()->id())->where('receiver_id',$receiver->id)->exists()){
                return redirect()->route('send.money.create')->with('success','Money Send Successfully');
            }


                $to = $receiver->email;
                $subject = " Money send successfully.";
                $msg = "Hello ".($receiver->company_name ?? $receiver->name)."!\nMoney send successfully.\nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                @mail($to,$subject,$msg,$headers);

            return redirect()->route('user.send.money.success');
        }else{
            return redirect()->back()->with('unsuccess','Sender not found!');
        }
    }

    public function saveAccount(Request $request){
        $savedUser = SaveAccount::whereUserId(auth()->id())->where('receiver_id',$request->receiver_id)->first();

        if($savedUser){
            return redirect()->route('send.money.create')->with('success','Already Saved.');
        }
        $data = new SaveAccount();

        $data->user_id = $request->user_id;
        $data->receiver_id = $request->receiver_id;
        $data->save();

        return redirect()->route('send.money.create')->with('success','Money Send Successfully');
    }

    public function cancle(){
        return redirect()->route('send.money.create')->with('success','Money Send Successfully');
    }


}
