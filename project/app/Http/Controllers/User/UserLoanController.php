<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\LoanPlan;
use App\Models\UserLoan;
use App\Models\Transaction;
use App\Models\Generalsetting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\InstallmentLog;
use App\Http\Controllers\Controller;

class UserLoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['loans'] = UserLoan::whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.loan.index',$data);
    }

    public function pending(){
        $data['loans'] = UserLoan::whereStatus(0)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.loan.pending',$data);
    }

    public function running(){
        $data['loans'] = UserLoan::whereStatus(1)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.loan.running',$data);
    }

    public function paid(){
        $data['loans'] = UserLoan::whereStatus(3)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.loan.paid',$data);
    }

    public function rejected(){
        $data['loans'] = UserLoan::whereStatus(2)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.loan.rejected',$data);
    }

    public function loanPlan(){
        $data['plans'] = LoanPlan::orderBy('id','desc')->whereStatus(1)->paginate(12);
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        $data['loans'] = UserLoan::whereUserId(auth()->id())->orderby('id','desc')->paginate(10);

        $wallets = Wallet::where('user_id',auth()->id())->where('wallet_type',4)->with('currency')->get();
        $data['wallets'] = $wallets;

        return view('user.loan.plan',$data);
    }

    public function loanAmount(Request $request){
        $plan = LoanPlan::whereId($request->planId)->first();
        $amount = $request->amount;

        if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
            $data['data'] = $plan;
            $data['loanAmount'] = $amount;
            $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
            $data['perInstallment'] = ($amount * $plan->per_installment)/100;
            return view('user.loan.apply',$data);
        }else{
            return redirect()->back()->with('warning','Request Money should be between minium and maximum amount!');
        }
    }

    public function loanfinish(Request $request){
        $loan = UserLoan::whereId($request->planId)->first();
        if($loan){
            $plan = LoanPlan::whereId($loan->plan_Id)->first();
            user_wallet_decrement($loan->user_id, $loan->currency_id, $loan->per_installment_amount*$loan->total_installment - $loan->paid_amount, 4);

            $loan->status = 3;
            $loan->next_installment = NULL;
            $loan->update();
            send_notification(auth()->id(), 'Loan finsih has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.loan.show', $loan->id));
            send_staff_telegram('Loan finsih has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.loan.show', $loan->id), 'Loan');

            return redirect()->back()->with('message','Finish Requesting Successfully');
        }else {
            return redirect()->back()->with('warning','There is not your loan plan');
        }
    }

    public function loanRequest(Request $request){

        $user = auth()->user();
        $gs = Generalsetting::first();
        if($user->bank_plan_id === null){
            return redirect()->route('user.loans.plan')->with('warning','You have to buy a plan to loan');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->route('user.loans.plan')->with('warning','Plan Date Expired.');
        }

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $monthlyLoans = UserLoan::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus('approve')->sum('loan_amount');

        if($monthlyLoans > $bank_plan->loan_amount){
            return redirect()->route('user.loans.plan')->with('warning','Monthly loan limit over.');
        }

        $data = new UserLoan();
        $input = $request->all();

        $loan = LoanPlan::findOrFail($request->plan_id);

        $requireInformations = [];
        if($loan->required_information){
            foreach(json_decode($loan->required_information) as $key=>$value){
                $requireInformations[$value->type][$key] = str_replace(' ', '_', $value->field_name);
            }
        }


        $details = [];
        foreach($requireInformations as $key=>$infos){

            foreach($infos as $index=>$info){

                if($request->has($info)){
                    if($request->hasFile($info)){
                        if ($file = $request->file($info))
                        {
                           $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                           $file->move('assets/images',$name);
                           $details[$info] = [$name,$key];
                        }
                    }else{
                        $details[$info] = [$request->$info,$key];
                    }
                }
            }
        }

        if(!empty($details)){
            $input['required_information'] = json_encode($details,true);
        }

        $txnid = Str::random(4).time();
        $input['transaction_no'] = $txnid;
        $input['user_id'] = auth()->id();
        $input['next_installment'] = now()->addDays($loan->installment_interval);
        $input['given_installment'] = 0;
        $input['paid_amount'] = 0;
        $input['total_amount'] = $request->loan_amount;
        $input['currency_id'] = $request->currency_id;
        $data->fill($input)->save();

        $trans = new Transaction();
        $trans->trnx = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->loan_amount;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'loan_create';
        $trans->details     = trans('loan requesting');
        $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'"}';
        $trans->save();
        send_notification(auth()->id(), 'Loan has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.loan.show', $data->id));
        send_staff_telegram('Loan has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.loan.show', $data->id), 'Loan');
        return redirect()->route('user.loans.index')->with('message','Loan Requesting Successfully');
    }

    public function log($id){
        $loan = UserLoan::findOrfail($id);
        $logs = InstallmentLog::whereTransactionNo($loan->transaction_no)->whereUserId(auth()->id())->orderby('id','desc')->paginate(20);
        $currency = Currency::whereId($loan->currency->id)->first();

        return view('user.loan.log',compact('logs','currency'));
    }
}
