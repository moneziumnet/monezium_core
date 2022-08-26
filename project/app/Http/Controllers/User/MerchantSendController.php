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
use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;

class MerchantSendController extends Controller
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

            return view('user.merchant.sendmoney.create',$data);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }

    }

    public function savedUser($no){
        // if(auth()->user()->twofa)
        // {
        //     $ga = new GoogleAuthenticator();
            $data['secret'] = $ga->createSecret();
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['savedUser'] = User::whereEmail($no)->first();
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

            return view('user.merchant.sendmoney.create',$data);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }
    }

    public function success(){
        if(session('saveData') && session('sendstatus') == 1){
            $data['data'] = session()->get('saveData');
            $data['user_id'] = auth()->user()->id;

            session(['sendstatus'=>0]);
            return view('user.merchant.sendmoney.success',$data);
        }else{
            session(['sendstatus'=>0]);
            $data['savedUser'] =  NULL;
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

            return view('user.merchant.sendmoney.create',$data);
        }
    }


    public function store(Request $request){

        $request->validate([
            'email'    => 'required',
            'wallet_id'         => 'required',
            'account_name'      => 'required',
            'amount'            => 'required|numeric|min:0',
            'description'       => 'required',
            'code'              => 'required'
        ]);

        $user = auth()->user();
        $ga = new GoogleAuthenticator();
        $secret = $user->go;
        $oneCode = $ga->getCode($secret);

        if ($oneCode != $request->code) {
            return redirect()->back()->with('unsuccess','Two factor authentication code is wrong');
        }

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }
        $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();

        $currency_id = $wallet->currency->id; //Currency::whereId($wallet_id)->first()->id;

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

        if($request->email == $user->email){
            return redirect()->back()->with('unsuccess','You can not send money yourself!!');
        }

        if($request->amount < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this!');
        }

        if($request->amount > user_wallet_balance(auth()->id(), $currency_id, $wallet->wallet_type)){
            return redirect()->back()->with('unsuccess','Insufficient Balance.');
        }

        if($receiver = User::where('email',$request->email)->first()){
            $txnid = Str::random(4).time();
            // $data = new BalanceTransfer();
            // $data->user_id = auth()->user()->id;
            // $data->receiver_id = $receiver->id;
            // $data->transaction_no = $txnid;
            // $data->currency_id = $request->wallet_id;
            // $data->type = 'own';
            // $data->cost = 0;
            // $data->amount = $request->amount;
            // $data->description = $request->description;
            // $data->status = 1;
            // $data->save();

            // $receiver->increment('balance',$request->amount);
            // $user->decrement('balance',$request->amount);

            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $user->id;
            $trans->user_type   = 2;
            $trans->currency_id = $currency_id;
            $trans->amount      = $request->amount;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Send_Money';
            $trans->details     = trans('Send Money');
            $trans->save();


            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $receiver->id;
            $trans->user_type   = 2;
            $trans->currency_id = $currency_id;
            $trans->amount      = $request->amount;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Recieve_Money';
            $trans->details     = trans('Send Money');
            $trans->save();

            session(['sendstatus'=>1, 'saveData'=>$trans]);
            // user_wallet_decrement($user->id, $currency_id, $request->amount);
            // user_wallet_increment($receiver->id, $currency_id, $request->amount);

            user_wallet_decrement($user->id, $currency_id, $request->amount, $wallet->wallet_type);
            user_wallet_increment($receiver->id, $currency_id, $request->amount, $wallet->wallet_type);
            if(SaveAccount::whereUserId(auth()->id())->where('receiver_id',$receiver->id)->exists()){
                return redirect()->route('user.merchant.send.money.create')->with('success','Money Send Successfully');
            }

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

            return redirect()->route('user.merchant.send.money.success');
        }else{
            return redirect()->back()->with('unsuccess','Sender not found!');
        }
    }

    public function saveAccount(Request $request){
        $savedUser = SaveAccount::whereUserId(auth()->id())->where('receiver_id',$request->receiver_id)->first();

        if($savedUser){
            return redirect()->route('user.merchant.send.money.create')->with('success','Already Saved.');
        }
        $data = new SaveAccount();

        $data->user_id = $request->user_id;
        $data->receiver_id = $request->receiver_id;
        $data->save();

        return redirect()->route('user.merchant.send.money.create')->with('success','Money Send Successfully');
    }

    public function cancle(){
        return redirect()->route('user.merchant.send.money.create')->with('success','Money Send Successfully');
    }


}
