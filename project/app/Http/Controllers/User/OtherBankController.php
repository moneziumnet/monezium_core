<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\PlanDetail;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\SubInsBank;
use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OtherBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function othersend($id){
        $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->pluck('subbank_id');
        $data['banks'] = SubInsBank::where('status', 1)->get();
        $data['data'] = Beneficiary::findOrFail($id);
        $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;
        $data['user'] = auth()->user();
        return view('user.otherbank.send',$data);
    }

    public function copysend($id){
        $data['beneficiary'] = BalanceTransfer::findOrFail($id);
        $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->pluck('subbank_id');
        $data['banks'] = SubInsBank::whereIn('id', $data['bankaccounts'])->get();
        $data['data'] = Beneficiary::findOrFail($data['beneficiary']->beneficiary_id);
        $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;
        $data['user'] = auth()->user();


        return view('user.otherbank.copy',$data);
    }

    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('External Payments')) {
            if ($user->two_fa_code != $request->otp) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $other_bank_limit =Generalsetting::first()->other_bank_limit;
        if ($request->amount >= $other_bank_limit) {
            $rules = [
                'document' => 'required|mimes:xls,xlsx,pdf,jpg,png'
            ];
        }
        else {
            $rules = [
                'document' => 'mimes:xls,xlsx,pdf,jpg,png'
            ];
        }


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess',$validator->getMessageBag()->toArray()['document'][0]);
        }


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

        if ($global_range) {
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
            }
            $finalAmount = $request->amount - $transaction_global_cost;

            if($global_range->min > $request->amount){
                return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$global_range->min);
            }

            if($global_range->max < $request->amount){
                return redirect()->back()->with('unsuccess','Request Amount should be less than this '.$global_range->max);
            }

            $currency = defaultCurr();
            $balance = user_wallet_balance(auth()->id(), $currency);

            if($balance<0 || $finalAmount > $balance){
                return redirect()->back()->with('unsuccess','Insufficient Balance!');
            }

            if($global_range->daily_limit <= $finalAmount){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }

            if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }


            if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your monthly limitation of transaction is over.');
            }



            // if($request->amount > $balance){
            //     return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
            // }
            $data = new BalanceTransfer();

            $txnid = Str::random(4).time();
            if ($file = $request->file('document'))
            {
                $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                $file->move('assets/doc',$name);
                $data->document = $name;
            }

            $data->user_id = auth()->user()->id;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->currency_id;
            $data->subbank = $request->subbank;
            $data->iban = $request->account_iban;
            $data->swift_bic = $request->swift_bic;
            $data->beneficiary_id = $request->beneficiary_id;
            $data->type = 'other';
            $data->cost = $transaction_global_cost;
            $data->payment_type = $request->payment_type;
            $data->amount = $request->amount;
            $data->final_amount = $finalAmount;
            $data->description = $request->des;
            $data->status = 0;
            $data->save();

            // $trans = new Transaction();
            // $trans->trnx = $txnid;
            // $trans->user_id     = $user->id;
            // $trans->user_type   = 1;
            // $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            // $trans->amount      = $finalAmount;
            // $trans->charge      = $cost;
            // $trans->type        = '-';
            // $trans->remark      = 'Send_Money';
            // $trans->data        = '{"sender":"'.$user->name.'", "receiver":"Other Bank"}';
            // $trans->details     = trans('Send Money');

            // // $trans->email = $user->email;
            // // $trans->amount = $finalAmount;
            // // $trans->type = "Send Money";
            // // $trans->profit = "minus";
            // // $trans->txnid = $txnid;
            // // $trans->user_id = $user->id;
            // $trans->save();

            // $user->decrement('balance',$finalAmount);
            // $currency = defaultCurr();
            // user_wallet_decrement(auth()->id(),$currency->id,$finalAmount);

            return redirect(route('user.beneficiaries.index'))->with('message','Money Send successfully.');

        }

    }
}
