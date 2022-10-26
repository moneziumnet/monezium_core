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
use App\Models\Wallet;

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
        $data['user'] = auth()->user();

        return view('user.withdraw.create' ,$data);
    }

    public function gateway(Request $request) {
        return DB::table('payment_gateways')->where('subins_id', $request->id)->whereStatus(1)->get();
    }

    public function gatewaycurrency(Request $request) {
        $currency['id'] = PaymentGateway::whereId($request->id)->whereStatus(1)->first();
        $res = [];
        foreach (json_decode($currency['id']->currency_id) as $value) {
            $code = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 1)->where('currency_id', $value)->with('currency')->first();
            if($code) {
                array_push($res,$code);
            }
        }
        return $res;
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if($user->paymentCheck('Withdraw')) {
            if ($user->two_fa_code != $request->otp) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $request->validate([
            'amount' => 'required|gt:0',
        ]);

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

        $currency = Currency::whereId($request->currency_id)->first();
        $rate = getRate($currency);
        if($dailyWithdraws > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlyWithdraws > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }

        if($request->amount/$rate > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }

        $transaction_global_cost = 0;
        if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'withdraw');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user, 'withdraw');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $messagefee = $transaction_global_cost + $transaction_custom_cost;
        $messagefinal = $request->amount - $messagefee*$rate;


        if($messagefinal < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')');
        }



        user_wallet_decrement($user->id, $currency->id, $request->amount);
        user_wallet_increment(0, $currency->id, $transaction_global_cost*$rate, 9);
        if($user->referral_id != 0) {
            $remark='withdraw_money_supervisor_fee';
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost*$rate, 6);
                $trans_wallet = get_wallet($user->referral_id, $request->currency_id, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark='withdraw_money_manager_fee';
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost*$rate, 10);
                $trans_wallet = get_wallet($user->referral_id, $request->currency_id, 10);
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->amount      = $transaction_custom_cost*$rate;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Withdraw money');
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'"}';
            $trans->save();
        }

        $txnid = Str::random(12);
        $newwithdrawal = new Withdrawals();
        $newwithdrawal->trx         = $txnid;
        $newwithdrawal->user_id = auth()->id();
        $newwithdrawal->method_id   = $request->methods;
        $newwithdrawal->currency_id = $currency->id;
        $newwithdrawal->amount      = $request->amount;
        $newwithdrawal->charge      = $messagefee*$rate;
        $newwithdrawal->total_amount= $messagefinal;
        $newwithdrawal->user_data   = $request->details;
        $newwithdrawal->save();




        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $currency->id;
        $trans->amount      = $request->amount;
        $trans_wallet = get_wallet($user->id, $currency->id);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $messagefee*$rate;
        $trans->type        = '-';
        $trans->remark      = 'withdraw_money';
        $trans->details     = trans('Withdraw money');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.Admin::findOrFail($request->subinstitude)->name.'"}';
        $trans->save();

        return redirect(route('user.withdraw.index'))->with('message','Withdraw Request Amount : '.$request->amount.' Fee : '.$messagefee*$rate.' = '.$messagefinal.' ('.$currency->code.') Sent Successfully.');

    }

    public function details($id){
        $data['data'] = Withdrawals::findOrFail($id);
        return view('user.withdraw.details',$data);
    }
}
