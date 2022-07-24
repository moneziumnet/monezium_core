<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BankPlan;
use App\Models\PaymentGateway;
use Auth;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdraw;
use App\Models\Withdrawals;
use App\Models\WithdrawMethod;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Charge;

class WithdrawController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

  	public function index()
    {
        $withdraws = Withdrawals::whereUserId(auth()->id())->orderBy('id','desc')->paginate(10);
        return view('user.withdraw.index',compact('withdraws'));
    }

    public function create()
    {
        // $data['methods'] = WithdrawMethod::whereStatus(1)->orderBy('id','desc')->get();
        $data['subinstitude'] = Admin::where('id', '!=', 1)->orderBy('id')->get();

        return view('user.withdraw.create' ,$data);
    }

    public function gateway(Request $request) {
        return DB::table('payment_gateways')->where('subins_id', $request->id)->whereStatus(1)->get();
    }

    public function gatewaycurrency(Request $request) {
        $currency['id'] = PaymentGateway::whereId($request->id)->whereStatus(1)->first();
        $res = [];
        foreach (json_decode($currency['id']->currency_id) as $value) {
            $code =  Currency::where('id',$value)->first();
            array_push($res,$code);
        }
        return $res;
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|gt:0',
        ]);

        $user = auth()->user();

        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

        // $withdraw_method = WithdrawMethod::whereId($request->methods)->first();
        // $withdraw_method = WithdrawMethod::whereId(1)->first();
        $withdraw_charge = Charge::where('plan_id',$user->bank_plan_id)->where('slug','transfer-money')->first()->value('data');
        $userBalance = user_wallet_balance($user->id,$request->currency_id,1);

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $dailyWithdraws = Withdrawals::whereDate('created_at', '=', date('Y-m-d'))->whereStatus('completed')->sum('amount');
        $monthlyWithdraws = Withdrawals::whereMonth('created_at', '=', date('m'))->whereStatus('completed')->sum('amount');

        if($dailyWithdraws > $bank_plan->daily_withdraw){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlyWithdraws > $bank_plan->monthly_withdraw){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }

        if($request->amount > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }

        $global_charge = Charge::where('name', 'Transfer Money')->where('plan_id', $user->bank_plan_id)->first();
        $global_cost = 0;
        $transaction_global_cost = 0;
        $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;
        if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $custom_cost = 0;
        $transaction_custom_cost = 0;
        if(check_user_type(3))
        {
            $custom_charge = Charge::where('name', 'Transfer Money')->where('user_id', $user->id)->first();
            if($custom_charge)
            {
                $custom_cost = $custom_charge->data->fixed_charge + ($request->amount/100) * $custom_charge->data->percent_charge;
            }
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user);
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }

        $charge = $withdraw_charge->fixed_charge;

        $messagefee = $global_cost + $transaction_global_cost + $custom_cost + $transaction_custom_cost;
        $messagefinal = $request->amount - $messagefee;

        $currency = Currency::whereId($request->currency_id)->first();

        if($messagefinal < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')');
        }



        user_wallet_decrement($user->id, $currency->id, $request->amount);
        if(check_user_type(3)) {
            user_wallet_increment($user->id, $currency->id, $custom_cost + $transaction_custom_cost, 6);
        }

        $txnid = Str::random(12);
        $newwithdrawal = new Withdrawals();
        // $newwithdraw['user_id'] = auth()->id();
        // $newwithdraw['method'] = $request->methods;
        // $newwithdraw['txnid'] = $txnid;

        // $newwithdraw['amount'] = $finalamount;
        // $newwithdraw['fee'] = $fee;
        // $newwithdraw['details'] = $request->details;
        // $newwithdraw->save();

        $newwithdrawal->trx         = Str::random(12);
        $newwithdrawal->user_id = auth()->id();
        $newwithdrawal->method_id   = $request->methods;
        // $newwithdrawal->method_id   = 1;
        $newwithdrawal->currency_id = $currency->id;
        $newwithdrawal->amount      = $request->amount;
        $newwithdrawal->charge      = $messagefee;
        $newwithdrawal->total_amount= $messagefinal;
        $newwithdrawal->user_data   = $request->details;
        $newwithdrawal->save();



        $total_amount = $newwithdrawal->amount + $newwithdrawal->fee;

        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
        $trans->amount      = $request->amount;
        $trans->charge      = $messagefee;
        $trans->type        = '-';
        $trans->remark      = 'Payout';
        $trans->details     = trans('Payout created');

        // $trans->email = $user->email;
        // $trans->amount = $finalamount;
        // $trans->type = "Payout";
        // $trans->profit = "minus";
        // $trans->txnid = $txnid;
        // $trans->user_id = $user->id;
        $trans->save();

        return redirect()->back()->with('success','Withdraw Request Amount : '.$request->amount.' Fee : '.$messagefee.' = '.$messagefinal.' ('.$currency->code.') Sent Successfully.');

    }

    public function details(Request $request, $id){
        $data['data'] = Withdrawals::findOrFail($id);
        return view('user.withdraw.details',$data);
    }
}
