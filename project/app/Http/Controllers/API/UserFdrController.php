<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\FdrPlan;
use App\Models\UserFdr;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;

class UserFdrController extends Controller
{
    public $successStatus = 200;

    /***FDR API**/

    public function fdrAmount(Request $request){
        try {
            $plan = FdrPlan::whereId($request->planId)->first();
            $amount = $request->amount;
            if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
                $data['data'] = $plan;
                $data['fdrAmount'] = $amount;
                $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Money should be between minium and maximum amount!']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function fdrRequest(Request $request){
        try {
            $gs = Generalsetting::first();
            if(user_wallet_balance(auth()->id(),$request->input('currency_id'), 3)  >= $request->fdr_amount){

                $data = new UserFdr();
                $plan = FdrPlan::findOrFail($request->plan_id);

                $data->transaction_no = Str::random(4).time();
                $data->user_id = auth()->id();
                $data->fdr_plan_id = $plan->id;
                $data->amount = $request->fdr_amount;
                $data->profit_type = $plan->interval_type;
                $data->profit_amount = $request->profit_amount;
                $data->interest_rate = $plan->interest_rate;
                $data->currency_id = $request->currency_id;

                if($plan->interval_type == 'partial'){
                    $data->next_profit_time = Carbon::now()->addDays($plan->interest_interval);
                }
                $data->matured_time = Carbon::now()->addDays($plan->matured_days);
                $data->status = 1;
                $data->save();

                user_wallet_decrement(auth()->id(),$request->input('currency_id'),$request->fdr_amount, 3);
                user_wallet_increment(auth()->id(),$request->input('currency_id'),$request->fdr_amount, 3);

                $trans = new Transaction();
                $trans->trnx = $data->transaction_no;
                $trans->user_id     = auth()->id();
                $trans->user_type   = 1;
                $trans->currency_id = $request->currency_id;
                $trans->amount      = $request->fdr_amount;
                $trans_wallet = get_wallet(auth()->id(),$request->input('currency_id'),3);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'Fdr_create';
                $trans->details     = trans('Fdr created');

                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();
                $currency = Currency::findOrFail($request->currency_id);
                mailSend('fdr_run',['amount'=>$request->fdr_amount, 'curr'=> $currency->code ], auth()->user());

                send_notification(auth()->id(), 'FDR has been requested by '.(auth()->user()->company_name ?? auth()->user()->name)."\n FDR Amount : ".$request->fdr_amount.$currency->code, route('admin.fdr.running'));
                send_staff_telegram('FDR has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.fdr.running'), 'Fdr');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'FDR Requesting Successfully']);
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You Don\'t have sufficient balance']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function finish(Request $request){
        try {
            $fdr = UserFdr::whereId($request->plan_id)->where('user_id', auth()->id())->first();
            if($fdr){
                // user_wallet_decrement($fdr->user_id, $fdr->currency_id, $fdr->fdr_amount, 4);
                $currency = $fdr->currency->id;
                user_wallet_increment($fdr->user_id, $currency, $fdr->amount, 3);
                user_wallet_decrement($fdr->user_id, $currency, $fdr->amount, 3);
                $fdr->next_profit_time = NULL;
                $fdr->status = 2;
                $fdr->update();
                mailSend('fdr_finish',[], auth()->user());

                send_notification(auth()->id(), 'FDR Finish has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.fdr.closed'));
                send_staff_telegram('FDR Finish has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.fdr.closed'), 'Fdr');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Finish Requesting Successfully']);
            }else {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'There is not your FDR plan']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

/**END FDR API**/


}
