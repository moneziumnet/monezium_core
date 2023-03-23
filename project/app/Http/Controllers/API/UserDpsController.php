<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Currency;
use App\Models\DpsPlan;
use App\Models\InstallmentLog;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\UserDps;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class UserDpsController extends Controller
{
    public $successStatus = 200;
/***DPS API**/
    public function planDetails(Request $request){
        try {
            $data['data'] = DpsPlan::findOrFail($request->planIddps);
            $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);

        }
    }

    public function dpsSubmit(Request $request){
        try {
            $user = auth()->user();
            $gs = Generalsetting::first();

            if(user_wallet_balance(auth()->id(),$request->input('currency_id'), 3) >= $request->per_installment){
                $data = new UserDps();

                $plan = DpsPlan::findOrFail($request->dps_plan_id);
                $data->transaction_no = Str::random(4).time();
                $data->user_id = auth()->id();
                $data->currency_id = $request->currency_id;
                $data->dps_plan_id = $plan->id;
                $data->per_installment = $plan->per_installment;
                $data->installment_interval = $plan->installment_interval;
                $data->total_installment = $plan->total_installment;
                $data->interest_rate = $plan->interest_rate;
                $data->given_installment = 1;
                $data->deposit_amount = $request->deposit_amount;
                $data->matured_amount = $request->matured_amount;
                $data->paid_amount = $request->per_installment;
                $data->status = 1;
                $data->next_installment = Carbon::now()->addDays($plan->installment_interval);
                $data->save();

                user_wallet_decrement(auth()->id(),$request->input('currency_id'),$request->per_installment, 3);
                user_wallet_increment(auth()->id(),$request->input('currency_id'),$request->per_installment, 3);

                $log = new InstallmentLog();
                $log->user_id = auth()->id();
                $log->transaction_no = $data->transaction_no;
                $log->type = 'dps';
                $log->amount = $request->per_installment;
                $log->save();

                $trans = new Transaction();
                $trans->trnx = $data->transaction_no;
                $trans->user_id     = auth()->id();
                $trans->user_type   = 1;
                $trans_wallet = get_wallet(auth()->id(),$request->input('currency_id'),3);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->currency_id = $request->currency_id;
                $trans->amount      = $request->per_installment;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'Dps_create';
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->details     = trans('Dps created');

                $trans->save();
                $currency = Currency::findOrFail($request->currency_id);
                mailSend('dps_run',['amount'=>$request->deposit_amount, 'curr'=> $currency->code ], auth()->user());
                send_notification(auth()->id(), 'Dps has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.dps.running'));
                send_staff_telegram('Dps has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.dps.running'), 'Dps');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'DPS application submitted']);
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You Don\'t have sufficient balance']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function finish(Request $request) {
        try {
            $dps = UserDps::whereId($request->plan_Id)->where('user_id', auth()->id())->first();
            if($dps){

                $dps->status = 2;
                $dps->next_installment = NULL;
                $dps->update();
                mailSend('dps_finish',[], auth()->user());
                user_wallet_increment($dps->user_id, $dps->currency_id, $dps->paid_amount, 3);
                user_wallet_decrement($dps->user_id, $dps->currency_id, $dps->paid_amount, 3);
                send_notification(auth()->id(), 'Dps Finish has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.dps.matured'));
                send_staff_telegram('Dps Finish has been requested by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.dps.matured'), 'Dps');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Finish Requesting Successfully']);
            }else {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'There is not your DPS plan']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function log($id){
        try {
            $dps = UserDps::findOrfail($id);
            $logs = InstallmentLog::whereTransactionNo($dps->transaction_no)->whereUserId(auth()->id())->orderby('id','desc')->orderby('id','desc')->paginate(20);
            $currency = Currency::whereId($dps->currency->id)->first();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success','data'=>compact('logs','currency')]);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
/**END DPS API**/


}
