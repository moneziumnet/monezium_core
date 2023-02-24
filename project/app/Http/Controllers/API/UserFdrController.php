<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\FdrPlan;
use App\Models\UserFdr;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFdrController extends Controller
{
    public $successStatus = 200;

    /***FDR API**/
    public function fdr_index(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['fdr'] = UserFdr::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningfdr(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['fdr'] = UserFdr::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function closedfdr(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['fdr'] = UserFdr::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    
    public function applyfdr(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $rules = [
                'plan_id'       => 'required',
                'amount'        => 'required',
                'currency_id'   => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            
            $plan_id = $request->plan_id;
            $amount = $request->amount;
            $currency_id = $request->input('currency_id');
            $plan = FdrPlan::whereId($request->plan_id)->first();
            if(!empty($plan))
            {
                
                if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
                   
                    // $data['data']           = $plan;
                    // $data['fdrAmount']      = $amount;
                    // $data['currencyinfo']   = Currency::whereId($request->currency_id)->first();
                    
                    
                    if(user_wallet_balance($user_id,$request->input('currency_id'), 4)  >= $amount){
    
                        $data = new UserFdr();
                        $plan = FdrPlan::findOrFail($request->plan_id);            
                        $profit_amount = ($amount * $plan->interest_rate)/100;

                        $data->transaction_no   = Str::random(4).time();
                        $data->user_id          = $user_id;
                        $data->fdr_plan_id      = $plan->id;
                        $data->amount           = $amount;
                        $data->profit_type      = $plan->interval_type;
                        $data->profit_amount    = $profit_amount;
                        $data->interest_rate    = $plan->interest_rate;
                        $data->currency_id      = $request->input('currency_id');
            
                        if($plan->interval_type == 'partial'){
                            $data->next_profit_time = Carbon::now()->addDays($plan->interest_interval);
                        }
                        $data->matured_time = Carbon::now()->addDays($plan->matured_days);
                        $data->status = 1;
                        $data->save();
            
                        //$user->decrement('balance',$request->fdr_amount);
                        user_wallet_decrement($user_id,$currency_id,$amount, 4);
            
                        $trans              = new Transaction();
                        $trans->trnx        = $data->transaction_no;
                        $trans->user_id     = $user_id;
                        $trans->user_type   = 1;
                        $trans->currency_id = $currency_id;
                        $trans_wallet = get_wallet($user_id,$currency_id,4);
                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                        $trans->amount      = $amount;
                        $trans->charge      = 0;
                        $trans->type        = '-';
                        $trans->remark      = 'Fdr_create';
                        $trans->details     = trans('Fdr created');
            
                        // $trans->email = auth()->user()->email;
                        // $trans->amount = $request->fdr_amount;
                        // $trans->type = "Fdr";
                        // $trans->profit = "minus";
                        // $trans->txnid = $data->transaction_no;
                        // $trans->user_id = auth()->id();
                        $trans->save();
                        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Loan Requesting Successfully']);
                    }else{
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You Don,t have sufficient balance']);
                    }
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Money should be between minium and maximum amount!']);
                }
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Invalid']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function finishfdr(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $rules = [
                'user_plan_id'       => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $user_plan_id = $request->user_plan_id;
            $fdr = UserFdr::whereId($user_plan_id)->first();

            if($fdr){
                // user_wallet_decrement($fdr->user_id, $fdr->currency_id, $fdr->fdr_amount, 4);
                $currency = $fdr->currency->id;
                user_wallet_increment($fdr->user_id, $currency, $fdr->amount, 4);
                $fdr->next_profit_time = NULL;
                $fdr->status = 2;
                $fdr->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'FDR Finish Requesting Successfully']);
            }else {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your FDR information wrong.']);
            }
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function fdrplan(Request $request)
    {
        try {
            $data['plans']          = FdrPlan::whereStatus(1)->orderby('id','desc')->paginate(10);
            $data['currencylist']   = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function fdrdetails(Request $request, $id)
    {
        try {
            $user_id = Auth::user()->id;

            if($id)
            {
                $fdr = UserFdr::whereUserId($user_id)->where('id',$id)->first();
                if(!empty($fdr))
                {
                    $data['fdr'] = $fdr;
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This FDR is not yours.']);
                }
                
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please pass the valid User DPS ID']);
            }
            
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/**END FDR API**/


}
