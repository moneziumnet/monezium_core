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
use App\Models\PlanDetail;

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
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $userBalance = user_wallet_balance($user->id,$request->currency_id,1);

        $dailyWithdraws = Withdrawals::whereDate('created_at', '=', date('Y-m-d'))->whereStatus('completed')->sum('amount');
        $monthlyWithdraws = Withdrawals::whereMonth('created_at', '=', date('m'))->whereStatus('completed')->sum('amount');

        if($dailyWithdraws > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlyWithdraws > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }

        if($request->amount > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }

        $transaction_global_cost = 0;
        if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user, 'withdraw');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user, 'withdraw');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $messagefee = $transaction_global_cost + $transaction_custom_cost;
        $messagefinal = $request->amount - $messagefee;

        $currency = Currency::whereId($request->currency_id)->first();

        if($messagefinal < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')');
        }



        user_wallet_decrement($user->id, $currency->id, $request->amount);
        if($user->referral_id != 0) {
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost, 10);
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;
            $trans->amount      = $transaction_custom_cost;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'withdraw_money_supervisor_fee';
            $trans->details     = trans('Withdraw money');
            $trans->save();
        }

        $txnid = Str::random(12);
        $newwithdrawal = new Withdrawals();
        $newwithdrawal->trx         = $txnid;
        $newwithdrawal->user_id = auth()->id();
        $newwithdrawal->method_id   = $request->methods;
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
        $trans->currency_id = $currency->id;
        $trans->amount      = $request->amount;
        $trans->charge      = $messagefee;
        $trans->type        = '-';
        $trans->remark      = 'withdraw_money';
        $trans->details     = trans('Withdraw money');
        $trans->save();

        return redirect()->back()->with('success','Withdraw Request Amount : '.$request->amount.' Fee : '.$messagefee.' = '.$messagefinal.' ('.$currency->code.') Sent Successfully.');

    }

    public function details(Request $request, $id){
        $data['data'] = Withdrawals::findOrFail($id);
        return view('user.withdraw.details',$data);
    }
}
