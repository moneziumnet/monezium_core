<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DepositBank;
use App\Models\PlanDetail;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;

class DepositBankController extends Controller
{
    public function datatables()
    {
        $datas = DepositBank::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(DepositBank $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(DepositBank $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->name;
                        })
                        ->addColumn('customer_email',function(DepositBank $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->email;
                        })
                        ->editColumn('amount', function(DepositBank $data) {
                            return $data->currency->symbol.round($data->amount*$data->currency->rate);
                        })
                        ->editColumn('status', function(DepositBank $data) {
                            $status = $data->status == 'pending' ? '<span class="badge badge-warning">pending</span>' : '<span class="badge badge-success">completed</span>';
                            return $status;
                        })
                        ->editColumn('action', function(DepositBank $data) {
                            $status      = $data->status == 'complete' ? _('completed') : _('pending');
                            $status_sign = $data->status == 'complete' ? 'success'   : 'danger';

                            @$detail = SubInsBank::where('id', $data->sub_bank_id)->first();
                            @$bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id', $detail->id)->where('currency_id', $data->currency_id)->with('user')->first();
                            $detail->address = str_replace(' ', '-', $detail->address);
                            $detail->name = str_replace(' ', '-', $detail->name);
                            $doc_url = $data->document ? $data->document : null;
                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              '.$status .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'complete']).'">'.__("Pending").'</a>
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'pending']).'">'.__("Completed").'</a>'.' <a href="javascript:;"  data-detail = \''.json_encode($detail).'\' data-bank= \''.json_encode($bankaccount).'\' data-docu="'.$doc_url.'" data-number="'.$data->deposit_number.'"  onclick=getDetails(event) class="dropdown-item detailsBtn" >
                              ' . __("Details") . '</a>'.'
                            </div>
                          </div>';
                        })
                        ->rawColumns(['created_at','customer_name','customer_email','amount','action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.depositbank.index');
    }

    public function status($id1,$id2){
        $data = DepositBank::findOrFail($id1);

        if($data->status == 'complete'){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        $user = User::findOrFail($data->user_id);
        $amount = $data->amount*$data->currency->rate;
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
            $remark = 'Deposit_create_supervisor_fee';
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'Deposit_create_manager_fee';
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost, 10);
            }
            $referral_user = User::findOrFail($user->referral_id);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $transaction_custom_cost;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Deposit complete');
            $trans->data        = '{"sender":"System Account", "receiver":"'.$referral_user->name.'"}';
            $trans->save();
        }
        $final_chargefee = $transaction_global_cost + $transaction_custom_cost;
        $final_amount = amount($amount - $final_chargefee, $data->currency->type );


        user_wallet_increment($user->id, $data->currency_id, $final_amount, 1);
        user_wallet_increment(0, 1, $transaction_global_cost, 9);




        $trans = new Transaction();
        $trans->trnx = $data->deposit_number;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;
        $trans->amount      = $amount;
        $trans->charge      = $final_chargefee;
        $trans->type        = '+';
        $trans->remark      = 'Deposit_create';
        $trans->details     = trans('Deposit complete');
        $trans->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
        $trans->save();

        $data->update(['status' => 'complete']);
        $gs = Generalsetting::findOrFail(1);

            $to = $user->email;
            $subject = " You have deposited successfully.";
            $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}

