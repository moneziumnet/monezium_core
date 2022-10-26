<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;

class MerchantOtherBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['beneficiaries'] = Beneficiary::where('user_id',auth()->user()->id)->orderBy('id','desc')->paginate(10);
        return view('user.merchant.otherbank.index',$data);
    }

    public function othersend($id){
        $data['data'] = Beneficiary::findOrFail($id);
        return view('user.merchant.otherbank.send',$data);
    }

    public function store(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        $user = auth()->user();
        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

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
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();

        $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereDate('created_at', now())->get();
        $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereMonth('created_at', now()->month())->get();
        $transaction_global_cost = 0;
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user, 'withdraw');
        if ($global_range ) {
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
            }
            $finalAmount = $request->amount + $transaction_global_cost;

            if($global_range->min > $request->amount){
                return redirect()->back()->with('unsuccess','Request Amount should be greater than this');
            }

            if($global_range->max_limit < $request->amount){
                return redirect()->back()->with('unsuccess','Request Amount should be less than this');
            }

            $currency = defaultCurr();
            $balance = user_wallet_balance(auth()->id(), $currency);


            if($global_range->daily_limit <= $finalAmount){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }

            if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }


            if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your monthly limitation of transaction is over.');
            }

            if($request->amount > $balance){
                return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
            }

            $txnid = Str::random(4).time();

            $data = new BalanceTransfer();
            $data->user_id = auth()->user()->id;
            $data->transaction_no = $txnid;
            $data->beneficiary_id = $request->beneficiary_id;
            $data->type = 'other';
            $data->cost = $transaction_global_cost;
            $data->amount = $request->amount;
            $data->final_amount = $finalAmount;
            $data->description = $request->des;
            $data->status = 0;
            $data->save();

            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $user->id;
            $trans->user_type   = 2;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans->amount      = $finalAmount;
            $trans_wallet = get_wallet(auth()->id(),$currency);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = $transaction_global_cost;
            $trans->type        = '-';
            $trans->remark      = 'Send_Money';
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"Other Bank", "description": "'.$data->description.'"}';
            $trans->details     = trans('Send Money');

            // $trans->email = $user->email;
            // $trans->amount = $finalAmount;
            // $trans->type = "Send Money";
            // $trans->profit = "minus";
            // $trans->txnid = $txnid;
            // $trans->user_id = $user->id;
            $trans->save();

            // $user->decrement('balance',$finalAmount);
            // $currency = defaultCurr();
            user_wallet_decrement(auth()->id(),$currency,$finalAmount);

            return redirect()->back()->with('success','Money Send successfully.');

        }

    }
}
