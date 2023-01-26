<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DpsPlan;
use App\Models\InstallmentLog;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\UserDps;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserDpsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



    public function planDetails(Request $request){
        $data['data'] = DpsPlan::findOrFail($request->planIddps);
        $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
        return view('user.dps.apply',$data);
    }

    public function finish(Request $request) {
        $dps = UserDps::whereId($request->plan_Id)->first();
        if($dps){

            $dps->status = 2;
            $dps->next_installment = NULL;
            $dps->update();
            user_wallet_increment($dps->user_id, $dps->currency_id, $dps->paid_amount, 3);
            user_wallet_decrement($dps->user_id, $dps->currency_id, $dps->paid_amount, 3);
            send_notification(auth()->id(), 'Dps Finish has been requested by '.auth()->user()->name.' Please check.', route('admin.dps.matured'));

            return redirect()->back()->with('message','Finish Requesting Successfully');
        }else {
            return redirect()->back()->with('warning','There is not your DPS plan');
        }

    }

    public function dpsSubmit(Request $request){
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
            //$user->decrement('balance',$request->per_installment);

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

            // $trans->email = auth()->user()->email;
            // $trans->amount = $request->per_installment;
            // $trans->type = "Dps";
            // $trans->profit = "minus";
            // $trans->txnid = $data->transaction_no;
            // $trans->user_id = auth()->id();
            $trans->save();
            send_notification(auth()->id(), 'Dps has been requested by '.auth()->user()->name.' Please check.', route('admin.dps.running'));

            return redirect()->route('user.invest.index')->with('success','DPS application submitted');
        }else{
            return redirect()->route('user.invest.index')->with('warning','You Don\'t have sufficient balance');
        }
    }

    public function log($id){
        $dps = UserDps::findOrfail($id);
        $logs = InstallmentLog::whereTransactionNo($dps->transaction_no)->whereUserId(auth()->id())->orderby('id','desc')->orderby('id','desc')->paginate(20);
        $currency = Currency::whereId($dps->currency->id)->first();

        return view('user.dps.log',compact('logs','currency'));
    }
}
