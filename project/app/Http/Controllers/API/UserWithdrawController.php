<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\BankPlan;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\Withdrawals;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserWithdrawController extends Controller
{
    public $successStatus = 200;

    public function withdraw(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['withdaw'] = Withdrawals::whereUserId($user_id)->orderBy('id','desc')->paginate(10); 
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    
    
    public function withdrawcreate(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $rules = [
                'subinstitude_id'        => 'required',
                'amount'                 => 'required|gt:0',
                'withdraw_method_id'     => 'required',
                'currency_id'            => 'required',
                'details'                => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $user = User::whereId($user_id)->first();

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(strtotime($user->plan_end_date) < strtotime()){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }
            
            $withdraw_charge = Charge::where('plan_id',$user->bank_plan_id)->where('slug','transfer-money')->first()->value('data');
            $userBalance = user_wallet_balance($user->id,$request->currency_id,1);

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailyWithdraws = Withdrawals::whereDate('created_at', '=', date('Y-m-d'))->whereStatus('completed')->sum('amount');
            $monthlyWithdraws = Withdrawals::whereMonth('created_at', '=', date('m'))->whereStatus('completed')->sum('amount');

            if($dailyWithdraws > $bank_plan->daily_withdraw){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily withdraw limit over.']);
            }

            if($monthlyWithdraws > $bank_plan->monthly_withdraw){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly withdraw limit over.']);
            }

            if($request->amount > $userBalance){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Account Balance.']);
            }

            $global_charge = Charge::where('name', 'Transfer Money')->where('plan_id', $user->bank_plan_id)->first();
            $global_cost = 0;
            $transaction_global_cost = 0;
            $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;
            if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum ]);
            }
            $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
            }
            $custom_cost = 0;
            $transaction_custom_cost = 0;
            $explode = explode(',',$user->user_type);

            if(in_array(3,$explode))
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
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be greater than this '.$request->amount.' ('.$currency->code.')']);
            }



            user_wallet_decrement($user->id, $currency->id, $request->amount);
            if(in_array(3,$explode))
            { 
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
            $newwithdrawal->user_id     = $user_id;
            $newwithdrawal->method_id   = $request->withdraw_method_id;
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
    
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Withdraw Request Amount : '.$request->amount.' Fee : '.$messagefee.' = '.$messagefinal.' ('.$currency->code.') Sent Successfully.']);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function withdrawdetails(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $rules = [
                'user_withdraw_id'        => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            
            $user_withdraw_id = $request->user_withdraw_id;

            $withdraw = Withdrawals::findOrFail($user_withdraw_id);
            
            if($withdraw)
            {
                $data['withdraws'] = $withdraw;
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            }

        }catch(\Throwable $th)
        {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }


}
