<?php

namespace App\Http\Controllers\User;

use Validator;
use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\SaveAccount;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;

class SendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(){
        $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
        $data['wallets'] = $wallets;
        $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();
        $data['savedUser'] = NULL;

        return view('user.sendmoney.create',$data);
    }

    public function savedUser($no){
        $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
        $data['wallets'] = $wallets;
        $data['savedUser'] = User::whereAccountNumber($no)->first();
        $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

        return view('user.sendmoney.create',$data);
    }

    public function success(){
        if(session('saveData') && session('sendstatus') == 1){
            $data['data'] = session()->get('saveData');

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

        $request->validate([
            'account_number'    => 'required',
            'wallet_id'         => 'required',
            'account_name'      => 'required',
            'amount'            => 'required|numeric|min:0',
            'description'       => 'required'
        ]);

        $user = auth()->user();

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

        $currency_id = Currency::whereIsDefault(1)->first()->id;
        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $dailySend = BalanceTransfer::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
        $monthlySend = BalanceTransfer::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

        if($dailySend > $bank_plan->daily_send){
            return redirect()->back()->with('unsuccess','Daily send limit over.');
        }

        if($monthlySend > $bank_plan->monthly_send){
            return redirect()->back()->with('unsuccess','Monthly send limit over.');
        }
        
        $gs = Generalsetting::first();

        if($request->account_number == $user->account_number){
            return redirect()->back()->with('unsuccess','You can not send money yourself!!');
        }

        if($request->amount < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this!');
        }

        if($request->amount > user_wallet_balance(auth()->id(), $currency_id)){
            return redirect()->back()->with('unsuccess','Insufficient Balance.');
        }
        
        if($receiver = User::where('account_number',$request->account_number)->first()){
            $txnid = Str::random(4).time();
            $data = new BalanceTransfer();
            $data->user_id = auth()->user()->id;
            $data->receiver_id = $receiver->id;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->wallet_id;
            $data->type = 'own';
            $data->cost = 0;
            $data->amount = $request->amount;
            $data->description = $request->description;
            $data->status = 1;
            $data->save();
    
            // $receiver->increment('balance',$request->amount);
            // $user->decrement('balance',$request->amount);

            user_wallet_decrement($user->id, $currency_id, $request->amount);
            user_wallet_increment($receiver->id, $currency_id, $request->amount);
            
            if(SaveAccount::whereUserId(auth()->id())->where('receiver_id',$data->receiver_id)->exists()){
                return redirect()->route('send.money.create')->with('success','Money Send Successfully');
            }

            session(['sendstatus'=>1, 'saveData'=>$data]);

            $trans = new Transaction();
            $trans->txnid = $txnid;
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans->amount      = $request->amount;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Send_Money';
            $trans->details     = trans('Send Money');

            // $trans->email = $user->email;
            // $trans->amount = $request->amount;
            // $trans->type = "Send Money";
            // $trans->profit = "minus";
            // $trans->txnid = $txnid;
            // $trans->user_id = $user->id;
            $trans->save();

            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $receiver->email,
                    'type' => "send money",
                    'cname' => $receiver->name,
                    'oamount' => $request->amount,
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

            return redirect()->route('user.send.money.success');
        }else{
            return redirect()->back()->with('unsuccess','Sender not found!');
        }
    }

    public function saveAccount(Request $request){
        $savedUser = SaveAccount::whereUserId(auth()->id())->where('receiver_id',$request->receiver_id)->first();

        if($savedUser){
            return redirect()->route('send.money.create')->with('success','Already in Beneficiary.');
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
