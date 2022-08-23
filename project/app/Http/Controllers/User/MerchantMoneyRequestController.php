<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Classes\GoogleAuthenticator;
use App\Models\MoneyRequest;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\MerchantShop;
use App\Http\Controllers\Controller;

class MerchantMoneyRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['requests'] = MoneyRequest::orderby('id','desc')->whereUserId(auth()->id())->where('user_type', 2)->paginate(10);
        return view('user.merchant.requestmoney.index',$data);
    }


    public function create(){
        $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
        $data['shop_list'] = MerchantShop::whereStatus(1)->where('merchant_id', auth()->id())->get();
        $data['wallets'] = $wallets;
        return view('user.merchant.requestmoney.create', $data);
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

        if($request->email == $user->email){
            return redirect()->back()->with('unsuccess','You can not send money yourself!');
        }

        $receiver = User::where('email',$request->email)->first();
        if($receiver === null){
            return redirect()->back()->with('unsuccess','No register user with this email!');
        }

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
        $data->receiver_id = $receiver->id;
        $data->receiver_name = $receiver->name;
        $data->transaction_no = $txnid;
        $data->currency_id = $request->wallet_id;
        $data->cost = $transaction_global_cost;
        $data->supervisor_cost = $user->referral_id != 0 ? $transaction_custom_cost : 0 ;
        $data->amount = $request->amount;
        $data->status = 0;
        $data->details = $request->details;
        $data->user_type = 2;
        $data->shop_id = $request->shop_id;
        $data->save();

        return redirect()->back()->with('success','Request Money Send Successfully.');
    }
    public function details($id){
        $data = MoneyRequest::findOrFail($id);
        $from = User::whereId($data->user_id)->first();
        $to = User::whereId($data->receiver_id)->first();
        return view('user.merchant.requestmoney.details',compact('data','from','to'));
    }
}
