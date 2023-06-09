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
use App\Classes\GoogleAuthenticator;
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
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
            else{
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
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

        // if(now()->gt($user->plan_end_date)){
        //     return redirect()->back()->with('unsuccess','Plan Date Expired.');
        // }

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $dailySend = BalanceTransfer::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
        $monthlySend = BalanceTransfer::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

        if($dailySend > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily send limit over.');
        }

        if($monthlySend > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly send limit over.');
        }


        $gs = Generalsetting::first();

        $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereDate('created_at', now())->get();
        $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereMonth('created_at', now()->month())->get();
        $transaction_global_cost = 0;
        $currency = Currency::findOrFail($request->currency_id);
        $rate = getRate($currency);
        $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'withdraw');

        if ($global_range) {
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            $finalAmount = $request->amount + $transaction_global_cost*$rate;

            if($global_range->min > $request->amount/$rate){
                return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$global_range->min);
            }

            if($global_range->max < $request->amount/$rate){
                return redirect()->back()->with('unsuccess','Request Amount should be less than this '.$global_range->max);
            }

            $balance = user_wallet_balance(auth()->id(), $request->currency_id);

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
            $data->cost = $transaction_global_cost*$rate;
            $data->payment_type = $request->payment_type;
            $data->amount = $request->amount + $transaction_global_cost*$rate;
            $data->final_amount = $request->amount;
            $data->description = preg_replace('/\p{Cf}+/u', '', $request->des);
            $data->status = 0;
            $data->save();

			user_wallet_decrement($user->id, $data->currency_id, $data->amount);
			user_wallet_increment(0, $data->currency_id, $data->cost, 9);

            $subbank = SubInsBank::findOrFail($request->subbank);
            mailSend('create_withdraw',['amount'=>amount($data->final_amount,1,2), 'trnx'=> $data->transaction_no,'curr' => $currency->code,'method'=>$subbank->name,'charge'=> amount($data->cost,1,2),'date_time'=> dateFormat($data->created_at)], $user);

            send_notification($user->id, 'Bank transfer has been created by '.(auth()->user()->company_name ?? auth()->user()->name).".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Pending", route('admin-user-banks', $user->id));
            send_staff_telegram('Bank transfer has been created by '.(auth()->user()->company_name ?? auth()->user()->name).".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Pending"."\n Please check.\n".route('admin-user-banks', $user->id), 'Bank Transfer');

            return redirect(route('user.beneficiaries.index'))->with('message','Money Send successfully.');

        }

    }
}
