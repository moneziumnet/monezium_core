<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\CryptoWithdraw;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Currency;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WithdrawCryptoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['withdraws'] = CryptoWithdraw::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.withdrawcrypto.index',$data);
    }

    public function create(){
        $data['cryptocurrencies'] = Currency::whereType(2)->get();
        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
        $data['user'] = auth()->user();
        return view('user.withdrawcrypto.create',$data);
    }


    public function store(Request $request){
        $user = auth()->user();
        if($user->payment_fa_yn == 'Y') {
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }

        $currency = Currency::where('id',$request->currency_id)->first();
        $userBalance = user_wallet_balance($request->user_id,$request->currency_id,8);
        $amountToAdd = $request->amount/$currency->rate;
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $dailywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value (USD) is '.$global_range->max.' and Min value(USD) is '.$global_range->min );

        }

        if($request->amount > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }

        if($dailywithdraw/$currency->rate > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlywithdraw/$currency->rate > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }

        $transaction_global_cost = 0;

        $transaction_global_fee = check_global_transaction_fee($amountToAdd, $user, 'withdraw');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amountToAdd/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($amountToAdd, $user, 'withdraw');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amountToAdd/100) * $transaction_custom_fee->data->percent_charge;
            }
        }

        $messagefee = $transaction_global_cost + $transaction_custom_cost;
        $messagefinal = $amountToAdd - $messagefee;

        if($messagefinal < 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')');
        }

        user_wallet_decrement($user->id, $currency->id, $amountToAdd*$currency->rate, 8);
        user_wallet_increment(0, $currency->id, $transaction_global_cost*$currency->rate, 9);

        if($user->referral_id != 0) {
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost*$currency->rate, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                user_wallet_increment($user->referral_id, $request->currency_id, $transaction_custom_cost*$currency->rate, 10);
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;
            $trans->amount      = $transaction_custom_cost*$currency->rate;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'withdraw_money_supervisor_fee';
            $trans->details     = trans('Withdraw money');
            $trans->data        = '{"sender":"'.$user->name.'", "receiver":"'.User::findOrFail($user->referral_id)->name.'"}';
            $trans->save();
        }

        $withdraw = new CryptoWithdraw();
        $input = $request->all();

        $withdraw->fill($input)->save();


        $txnid = Str::random(12);


        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = $messagefee*$currency->rate;
        $trans->type        = '-';
        $trans->remark      = 'withdraw_money';
        $trans->details     = trans('Withdraw money');
        $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
        $trans->save();



        return redirect()->route('user.cryptowithdraw.create')->with('success','Withdraw amount '.$request->amount.' ('.$currency->code.') successfully!');
    }


}
