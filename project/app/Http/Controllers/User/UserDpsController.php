<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DpsPlan;
use App\Models\InstallmentLog;
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

    public function index(){
        $data['dps'] = UserDps::whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.dps.index',$data);
    }

    public function running(){
        $data['dps'] = UserDps::whereStatus(1)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.dps.running',$data);
    }

    public function matured(){
        $data['dps'] = UserDps::whereStatus(2)->whereUserId(auth()->id())->orderby('id','desc')->paginate(10);
        return view('user.dps.matured',$data);
    }

    public function dpsPlan(){
        $data['plans'] = DpsPlan::orderBy('id','desc')->whereStatus(1)->orderby('id','desc')->paginate(12);
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        return view('user.dps.plan',$data);
    }

    public function planDetails(Request $request){
        $data['data'] = DpsPlan::findOrFail($request->planId);
        $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
        return view('user.dps.apply',$data);
    }

    public function dpsSubmit(Request $request){
        $user = auth()->user();

        if(user_wallet_balance(auth()->id(),$request->input('currency_id'), 4) >= $request->per_installment){
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

            user_wallet_decrement(auth()->id(),$request->input('currency_id'),$request->per_installment, 4);
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
            $trans->currency_id = $request->currency_id;
            $trans->amount      = $request->per_installment;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Dps_create';
            $trans->details     = trans('Dps created');

            // $trans->email = auth()->user()->email;
            // $trans->amount = $request->per_installment;
            // $trans->type = "Dps";
            // $trans->profit = "minus";
            // $trans->txnid = $data->transaction_no;
            // $trans->user_id = auth()->id();
            $trans->save();

            return redirect()->route('user.dps.index')->with('success','DPS application submitted');
        }else{
            return redirect()->route('user.dps.plan')->with('warning','You Don,t have sufficient balance');
        }
    }

    public function log($id){
        $dps = UserDps::findOrfail($id);
        $logs = InstallmentLog::whereTransactionNo($dps->transaction_no)->whereUserId(auth()->id())->orderby('id','desc')->orderby('id','desc')->paginate(20);
        $currency = Currency::whereId($dps->currency->id)->first();

        return view('user.dps.log',compact('logs','currency'));
    }
}
