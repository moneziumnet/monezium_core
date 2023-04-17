<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Charge;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use Datatables;
use DB;

class DepositController extends Controller
{
    public function datatables()
    {
        $datas = Deposit::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(Deposit $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(Deposit $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->company_name ?? $data->name;
                        })
                        ->addColumn('customer_email',function(Deposit $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->email;
                        })
                        ->editColumn('amount', function(Deposit $data) {
                            $gs = Generalsetting::find(1);
                            return $data->currency->symbol.round($data->amount*getRate($data->currency));
                        })
                        ->editColumn('status', function(Deposit $data) {
                            $status = $data->status == 'pending' ? '<span class="badge badge-warning">pending</span>' : '<span class="badge badge-success">completed</span>';
                            return $status;
                        })
                        ->editColumn('status', function(Deposit $data) {
                            $status      = $data->status == 'complete' ? _('completed') : _('pending');
                            $status_sign = $data->status == 'complete' ? 'success'   : 'danger';

                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              '.$status .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.deposits.status',['id1' => $data->id, 'id2' => 'complete']).'">'.__("Pending").'</a>
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.deposits.status',['id1' => $data->id, 'id2' => 'pending']).'">'.__("Completed").'</a>
                            </div>
                          </div>';
                        })
                        ->rawColumns(['created_at','customer_name','customer_email','amount','status'])
                        ->toJson();
    }

    public function index(){
        return view('admin.deposit.index');
    }

    public function status($id1,$id2){
        $gs = Generalsetting::findOrFail(1);
        $data = Deposit::findOrFail($id1);

        if($data->status == 'complete'){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        $user = User::findOrFail($data->user_id);
        $rate =  getRate($data->currency);
        $amount = $data->amount/$rate;
        $transaction_global_cost = 0;

        $transaction_global_fee = check_global_transaction_fee($amount, $user, 'deposit');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;

        $explode = explode(',',User::whereId($user->id)->first()->user_type);

        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'deposit');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
            }
            $remark = 'Deposit_supervisor_fee';
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 6);
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'Deposit_manager_fee';
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 10);
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 10);
            }
            $referral_user = User::findOrFail($user->referral_id);
            $supervisor_trnx = str_rand();

            $trans = new Transaction();
            $trans->trnx = $supervisor_trnx;
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $transaction_custom_cost*$rate;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Deposit complete');

            $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($referral_user->company_name ?? $referral_user->name).'"}, "description":"'.$data->deposit_number.'"}';
            $trans->save();

        }
        $final_chargefee = $transaction_global_cost + $transaction_custom_cost;
        $final_amount = $data->amount - $final_chargefee*$rate;

        user_wallet_increment(0, $data->currency_id, $transaction_global_cost*$rate, 9);
        user_wallet_increment($user->id, $data->currency_id, $final_amount, 1);

        $trans_wallet = get_wallet($user->id, $data->currency_id, 1);

        $trans = new Transaction();
        $trans->trnx = $data->deposit_number;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;
        $trans->amount      = $data->amount;

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trans->charge      = $final_chargefee*$rate;
        $trans->type        = '+';
        $trans->remark      = 'Deposit';
        $trans->details     = trans('Deposit complete');

        $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}, "description":"'.$data->deposit_number.'"}';
        $trans->save();

        $data->update(['status' => 'complete']);
        $currency = Currency::findOrFail($data->currency_id);
        mailSend('deposit_approved',['amount'=>$data->amount, 'curr' => $currency->code, 'trnx' => $data->deposit_number ,'date_time'=>$trans->created_at ,'type' => $data->method ], $user);
        send_notification($user->id, $data->method.' Deposit for '.($user->company_name ?? $user->name).'is approved '."\n Amount is ".$currency->symbol.$data->amount."\n Transaction ID : ".$data->deposit_number, route('admin.deposits.index'));


        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}

