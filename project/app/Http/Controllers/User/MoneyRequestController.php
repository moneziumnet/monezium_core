<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Charge;
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

        $gs = Generalsetting::first();

        if($request->account_number == $user->account_number){
            return redirect()->back()->with('unsuccess','You can not send money yourself!');
        }

        $receiver = User::where('account_number',$request->account_number)->first();
        if($receiver === null){
            return redirect()->back()->with('unsuccess','No register user with this email!');
        }

        if($dailyRequests > $bank_plan->daily_receive){
            return redirect()->back()->with('unsuccess','Daily request limit over.');
        }

        if($monthlyRequests > $bank_plan->monthly_receive){
            return redirect()->back()->with('unsuccess','Monthly request limit over.');
        }
        $global_charge = Charge::where('name', 'Request Money')->where('plan_id', $user->bank_plan_id)->first();
        $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;

        if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
        $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        if(check_user_type(3))
        {
            $custom_charge = Charge::where('name', 'Request Money')->where('user_id', $user->id)->first();
            $custom_cost = 0;
            $transaction_custom_cost = 0;
            if($custom_charge)
            {
                $custom_cost = $custom_charge->data->fixed_charge + ($request->amount/100) * $custom_charge->data->percent_charge;
            }
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user);
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $txnid = Str::random(4).time();

        $data = new MoneyRequest();
        $data->user_id = auth()->user()->id;
        $data->receiver_id = $receiver->id;
        $data->receiver_name = $receiver->name;
        $data->transaction_no = $txnid;
        $data->currency_id = $request->wallet_id;
        $data->cost = $global_cost + $transaction_global_cost;
        $data->supervisor_cost = check_user_type(3) ? $custom_cost + $transaction_custom_cost : 0 ;
        $data->amount = $request->amount;
        $data->status = 0;
        $data->details = $request->details;
        $data->user_type = 1;
        $data->save();

        // $trans = new Transaction();
        // $trans->trnx = $txnid;
        // $trans->user_id     = $user->id;
        // $trans->user_type   = 1;
        // $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
        // $trans->amount      = $finalAmount;
        // $trans->charge      = 0;
        // $trans->type        = '+';
        // $trans->remark      = 'Request_Money';
        // $trans->details     = trans('Request Money');

        // $trans->email = $user->email;
        // $trans->amount = $finalAmount;
        // $trans->type = "Request Money";
        // $trans->profit = "plus";
        // $trans->txnid = $txnid;
        // $trans->user_id = $user->id;
        // $trans->save();

        return redirect()->back()->with('success','Request Money Send Successfully.');

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
        user_wallet_increment($receiver->id, $currency_id, $data->supervisor_cost,6);
        user_wallet_increment($receiver->id, $currency_id, $finalAmount);

        // $sender->decrement('balance',$data->amount);
        // $receiver->increment('balance',$finalAmount);

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

        // $trans->email = auth()->user()->email;
        // $trans->amount = $data->amount;
        // $trans->type = "Request Money";
        // $trans->profit = "minus";
        // $trans->txnid = $data->transaction_no;
        // $trans->user_id = auth()->id();
        $trans->save();

        $trans = new Transaction();
        $trans->trnx = $data->transaction_no;
        $trans->user_id     = $receiver->id;
        $trans->user_type   = $data->user_type;
        $trans->currency_id = $currency_id;
        $trans->amount      = $finalAmount;
        $trans->charge      = $data->cost + $data->supervisor_cost;
        $trans->type        = '+';
        $trans->remark      = 'Request_Money';
        $trans->details     = trans('Request Money');

        // $trans->email = $receiver->email;
        // $trans->amount = $data->amount;
        // $trans->type = "Request Money";
        // $trans->profit = "plus";
        // $trans->txnid = $data->transaction_no;
        // $trans->user_id = $receiver->id;
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
