<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\PlanDetail;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Classes\GoogleAuthenticator;
use App\Models\MoneyRequest;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class MoneyRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['requests'] = MoneyRequest::orderby('id','desc')->whereUserId(auth()->id())->where('user_type', 1)->paginate(10);
        return view('user.requestmoney.index',$data);
    }

    public function receive(){
        if(auth()->user()->twofa)
        {
            $data['requests'] = MoneyRequest::orderby('id','desc')->whereReceiverId(auth()->id())->paginate(10);
            return view('user.requestmoney.receive',$data);
        }else{
            return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        }
    }

    public function create(){
        $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
        $data['wallets'] = $wallets;
        return view('user.requestmoney.create', $data);
    }

    public function store(Request $request){
        $request->validate([
            'account_name' => 'required',
            'wallet_id' => 'required',
            'amount' => 'required|gt:0',
        ]);

        $user = auth()->user();

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $dailyRequests = MoneyRequest::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
        $monthlyRequests = MoneyRequest::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'recieve')->first();


        $gs = Generalsetting::first();

        if($request->account_email == $user->email){
            return redirect()->back()->with('unsuccess','You can not send money yourself!');
        }

        $receiver = User::where('email',$request->account_email)->first();

        if($dailyRequests > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily request limit over.');
        }

        if($monthlyRequests > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly request limit over.');
        }

        if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user, 'recieve');
        $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        if($user->referral_id != 0)
        {
            $transaction_custom_cost = 0;
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user, 'recieve');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $txnid = Str::random(4).time();

        $data = new MoneyRequest();
        $data->user_id = auth()->user()->id;
        $data->receiver_id = $receiver === null ? 0 : $receiver->id;
        $data->receiver_name = $request->account_name;
        $data->transaction_no = $txnid;
        $data->currency_id = $request->wallet_id;
        $data->cost = $transaction_global_cost;
        $data->supervisor_cost = $user->referral_id != 0 ? $transaction_custom_cost : 0 ;
        $data->amount = $request->amount;
        $data->status = 0;
        $data->details = $request->details;
        $data->user_type = 1;
        $data->save();

        $currency = Currency::findOrFail($request->wallet_id);
        if($receiver === null){
            $gs = Generalsetting::first();
            $to = $request->account_email;
            $subject = " Money Request";
            $msg = "Hello ".$request->account_name."!\nYou received request money (".$request->amount.$currency->symbol.").\nPlease confirm current.\n".route('user.request.money.receive')."\n Thank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
            return redirect()->back()->with('success','Request Money Send to unregisted user('.$request->account_email.') Successfully.');
        }
        else {
            return redirect()->back()->with('success','Request Money Send Successfully.');
        }

    }

    public function verify($id)
    {
        if(auth()->user()->twofa)
        {
            $data['id'] = $id;
            return view('user.requestmoney.verify', $data);
        }else{
            return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        }
    }

    public function send(Request $request, $id){
        if(auth()->user()->twofa != 1)
        {
            return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        }

        $request->validate([
            'code' => 'required'
        ]);

        $user = auth()->user();
        $ga = new GoogleAuthenticator();
        $secret = $user->go;
        $oneCode = $ga->getCode($secret);

        if ($oneCode != $request->code) {
            return redirect()->back()->with('unsuccess','Two factor authentication code is wrong');
        }

        $data = MoneyRequest::findOrFail($id);
        $gs = Generalsetting::first();

        $currency_id = $data->currency_id;
        $sender = User::whereId($data->receiver_id)->first();
        $receiver = User::whereId($data->user_id)->first();

        if($data->amount > user_wallet_balance($sender->id, $currency_id)){
            return back()->with('warning','You don,t have sufficient balance!');
        }

        $finalAmount = $data->amount - $data->cost -$data->supervisor_cost;

        user_wallet_decrement($sender->id, $currency_id, $data->amount);
        if ($receiver->referral_id != 0) {

            user_wallet_increment($receiver->referral_id, $currency_id, $data->supervisor_cost,6);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $receiver->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency_id;
            $trans->amount      = $data->supervisor_cost;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Request_money_supervisor_fee';
            $trans->details     = trans('Request Money');
            $trans->save();
        }
        user_wallet_increment($receiver->id, $currency_id, $finalAmount);


        $data->update(['status'=>1]);

        $trans = new Transaction();
        $trans->trnx = $data->transaction_no;
        $trans->user_id     = auth()->id();
        $trans->user_type   = $data->user_type;
        $trans->currency_id = $currency_id;
        $trans->amount      = $data->amount;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'Request_Money';
        $trans->details     = trans('Request Money');

        $trans->save();

        $trans = new Transaction();
        $trans->trnx = $data->transaction_no;
        $trans->user_id     = $receiver->id;
        $trans->user_type   = $data->user_type;
        $trans->currency_id = $currency_id;
        $trans->amount      = $data->amount;
        $trans->charge      = $data->cost + $data->supervisor_cost;
        $trans->type        = '+';
        $trans->remark      = 'Request_Money';
        $trans->details     = trans('Request Money');

        $trans->save();

        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $receiver->email,
                'type' => "request money",
                'cname' => $receiver->name,
                'oamount' => $finalAmount,
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
            $to = $receiver->email;
            $subject = " Money send successfully.";
            $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
        }
        return redirect()->route('user.request.money.receive')->with('message','Successfully Money Send.');
        //return back()->with('message','Successfully Money Send.');
    }

    public function cancel($id)
    {
        $data = MoneyRequest::findOrFail($id);
        $data->update(['status'=>2]);
        return back()->with('message','Successfully Money Request Cancelled.');
    }

    public function details($id){
        $data = MoneyRequest::findOrFail($id);
        $from = User::whereId($data->user_id)->first();
        $to = User::whereId($data->receiver_id)->first();
        return view('user.requestmoney.details',compact('data','from','to'));
    }
}
