<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\BalanceTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserOtherBankController extends Controller
{
    public $successStatus = 200;

    public function otherbanktransfer(Request $request)
    {
        try{
            $user_id = Auth::user()->id;

            $rules = [
                'beneficiary_id'       => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $beneficiary_id = $request->beneficiary_id;
            $data['beneficiaries'] = Beneficiary::whereUserId($user_id)->where('id', $beneficiary_id)->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function otherbank(Request $request)
    {
        try{
           // $user_id = Auth::user()->id;

            $data['otherBanks'] = OtherBank::orderBy('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function otherbanksend(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $rules = [
                'other_bank_id'       => 'required',
                'beneficiary_id'       => 'required',
                'description'       => 'required',
                'amount' => 'required|numeric|min:0'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $other_bank_id = $request->other_bank_id;
            $amount = $request->amount;

            $user = User::whereId($user_id)->first();
            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(strtotime($user->plan_end_date)< strtotime(date('Y-m-dH:i:s'))){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailySend = BalanceTransfer::whereUserId($user_id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

            if($dailySend > $bank_plan->daily_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily send limit over.']);
            }

            if($monthlySend > $bank_plan->monthly_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly send limit over.']);
            }

            $gs = Generalsetting::first();
            $otherBank = OtherBank::whereId($request->other_bank_id)->first();
            $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId($user_id)->whereDate('created_at', now())->get();
            $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId($user_id)->whereMonth('created_at', now()->month())->get();

            if ($otherBank ) {
                $cost = $otherBank->fixed_charge + ($request->amount/100) * $otherBank->percent_charge;
                $finalAmount = $request->amount + $cost;

                if($otherBank->min_limit > $request->amount){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be greater than this']);
                }

                if($otherBank->max_limit < $request->amount){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be less than this']);
                }

                $currency = defaultCurr();
                $balance = user_wallet_balance($user_id, $currency);

                if($balance<0 && $finalAmount > $balance){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance!']);
                }

                if($otherBank->daily_maximum_limit <= $finalAmount){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your daily limitation of transaction is over.']);
                }

                if($otherBank->daily_maximum_limit <= $dailyTransactions->sum('final_amount')){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your daily limitation of transaction is over.']);
                }

                if($otherBank->daily_total_transaction <= count($dailyTransactions)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your daily number of transaction is over.']);
                }

                if($otherBank->monthly_maximum_limit < $monthlyTransactions->sum('final_amount')){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your monthly limitation of transaction is over.']);
                }

                if($otherBank->monthly_total_transaction <= count($monthlyTransactions)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your monthly number of transaction is over!']);
                }

                if($request->amount > $balance){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Account Balance.']);
                }

                $txnid = Str::random(4).time();

                $data = new BalanceTransfer();
                $data->user_id =$user_id;
                $data->transaction_no = $txnid;
                $data->other_bank_id = $request->other_bank_id;
                $data->beneficiary_id = $request->beneficiary_id;
                $data->type = 'other';
                $data->cost = $cost;
                $data->amount = $request->amount;
                $data->final_amount = $finalAmount;
                $data->description = $request->description;
                $data->status = 0;
                $data->save();

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = Currency::whereIsDefault(1)->first()->id;

                $trans_wallet = get_wallet($user_id,$currency);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->amount      = $finalAmount;
                $trans->charge      = $cost;
                $trans->type        = '-';
                $trans->remark      = 'Send_Money';
                $trans->details     = trans('Send Money');
                $trans->data        = '{"description":"'.$request->description.'"}';
                // $trans->email = $user->email;
                // $trans->amount = $finalAmount;
                // $trans->type = "Send Money";
                // $trans->profit = "minus";
                // $trans->txnid = $txnid;
                // $trans->user_id = $user->id;
                $trans->save();

                // $user->decrement('balance',$finalAmount);
                // $currency = defaultCurr();
                user_wallet_decrement($user_id,$currency,$finalAmount);
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Send successfully.']);

            }



        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
