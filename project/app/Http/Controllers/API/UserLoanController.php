<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BankPlan;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Models\SaveAccount;
use App\Models\Notification;
use App\Models\Withdrawals;
use App\Models\Currency;
use App\Models\Beneficiary;
use App\Models\UserLoan;

use App\Models\LoanPlan;

use App\Models\MoneyRequest;
use App\Models\InstallmentLog;

use App\Models\Charge;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserLoanController extends Controller
{
    public $successStatus = 200;


//////////////////////////////////////////////// Loan api ////////////////////////////////////////////////////
    public function loan_index(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['loans'] = UserLoan::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanplan(Request $request)
    {
        try {
           // $user_id = Auth::user()->id;
            //if ($user_id)
           // {
                $data['plans'] = LoanPlan::orderBy('id','desc')->whereStatus(1)->paginate(12);
                $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            //}
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function pendingloan(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['loans'] = UserLoan::whereStatus(0)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningloan(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['loans'] = UserLoan::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function paidloan(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['loans'] = UserLoan::whereStatus(3)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function rejectedloan(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['loans'] = UserLoan::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanamount(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $rules = [
                'planId'   => 'required',
                'amount'    => 'required',
                'currency_id'    => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            if ($user_id) {
                $plan = LoanPlan::whereId($request->planId)->first();
                $amount = $request->amount;

                if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
                    $data['data'] = $plan;
                    $data['loanAmount'] = $amount;
                    $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
                    $data['perInstallment'] = ($amount * $plan->per_installment)/100;
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Money should be between minium and maximum amount!']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }

    }

    public function loanrequest(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $user = User::whereId($user_id)->first();
            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to loan.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $monthlyLoans = UserLoan::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus('approve')->sum('loan_amount');

            if($monthlyLoans > $bank_plan->loan_amount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly loan limit over.']);
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
            $input['user_id'] = $user_id;
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
            $trans->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Loan Requesting Successfully']);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanfinish(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id)
            {
                $loan = UserLoan::whereId($request->planId)->where('user_id', $user_id)->first();
                if($loan){
                    $plan = LoanPlan::whereId($loan->planId)->first();
                    user_wallet_decrement($loan->user_id, $loan->currency_id, $loan->loan_amount, 4);

                    $loan->status = 3;
                    $loan->next_installment = NULL;
                    $loan->update();
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Finish Requesting Successfully']);
                }else {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'There is not your loan plan.']);

                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanlog(Request $request, $id)
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id) {
                $loan = UserLoan::findOrfail($id);
                $logs = InstallmentLog::whereTransactionNo($loan->transaction_no)->whereUserId($user_id)->orderby('id','desc')->paginate(20);
                $currency = Currency::whereId($loan->currency->id)->first();
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('logs','currency')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }


}
