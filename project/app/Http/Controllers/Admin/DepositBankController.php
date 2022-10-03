<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DepositBank;
use App\Models\PlanDetail;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\BankPoolAccount;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WebhookRequest;
use Illuminate\Http\Request;
use Datatables;

class DepositBankController extends Controller
{
    public function datatables()
    {
        $datas = DepositBank::orderBy('id','desc');

        return Datatables::of($datas)
            ->setRowAttr([
                'style' => function(DepositBank $data) {
                    $reference = $data->deposit_number;
                    $webhook_request = WebhookRequest::where('reference', $reference)->first();
                    if($data->status == 'pending' && (!$webhook_request || $webhook_request->status == "processing")) {
                        return "background-color: #ffcaca;";
                    } else {
                        return "background-color: #ffffff;";
                    }
                },
            ])
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
                if($data->status == 'pending') {
                    $status = '<span class="badge badge-warning">pending</span>';
                } elseif ($data->status == 'complete') {
                    $status = '<span class="badge badge-success">completed</span>';
                } else {
                    $status = '<span class="badge badge-danger">rejected</span>';
                }
                return $status;
            })
            ->addColumn('action', function(DepositBank $data) {

                @$detail = SubInsBank::where('id', $data->sub_bank_id)->with('subInstitution')->first();
                if($detail->hasGateway()){
                @$bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id', $detail->id)->where('currency_id', $data->currency_id)->with('user')->first();
                } else {
                @$bankaccount = BankPoolAccount::where('bank_id', $detail->id)->where('currency_id', $data->currency_id)->first();
                }
                $send_info = WebhookRequest::where('reference', $data->deposit_number)->with('currency')->first();
                $detail->address = str_replace(' ', '-', $detail->address);
                $detail->name = str_replace(' ', '-', $detail->name);
                $doc_url = $data->document ? $data->document : null;
                if($doc_url){
                    $arr_file_name = explode('.', $data->document);
                    $extension = $arr_file_name[count($arr_file_name) - 1];

                    if(in_array($extension, array('doc','docx','xls','xlsx','pdf')))
                        $doc_url = "https://docs.google.com/gview?url=".asset('assets/doc/'.$data->document);
                    else
                        $doc_url = asset('assets/doc/'.$data->document);
                }

                return '<div class="btn-group mb-1">
                    <a href="javascript:;"
                        data-detail = \''.json_encode($detail).'\'
                        data-sendinfo = \''.json_encode($send_info).'\'
                        data-bank= \''.json_encode($bankaccount).'\'
                        data-docu="'.$doc_url.'"
                        data-number="'.$data->deposit_number.'"
                        data-description="'.$data->details.'"
                        data-hasgateway = "'.json_encode($detail->hasGateway()).'"
                        data-status="'.$data->status.'"
                        data-complete-url="'.route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'complete']).'"
                        data-reject-url="'.route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'reject']).'"
                        onclick=getDetails(event)
                        class="btn btn-sm btn-primary detailsBtn">' . __("Details") . '</a>
                </div>';
            })
            ->rawColumns(['created_at','customer_name','customer_email','amount','status', 'action'])
            ->toJson();
    }

    public function index(){
        return view('admin.depositbank.index');
    }

    public function status($id1,$id2){
        $data = DepositBank::findOrFail($id1);

        if($data->status == 'complete' || $data->status == 'reject'){
          $msg = $data->status == 'complete'
            ? 'Deposits already completed'
            : 'Deposits already rejected';
          return redirect()->back()->with("error", $msg);
        }
        $webhook_request = WebhookRequest::where('reference', $data->deposit_number)
            ->where('gateway_type', 'openpayd')
            ->first();
        if($webhook_request) {
            $data->amount = $webhook_request->amount;
        }
        $data->status = $id2;
        $data->save();
        if($id2 == 'reject') {
            $msg = 'Data Updated Successfully.';
            return redirect()->back()->with("message", $msg);
        }

        $user = User::findOrFail($data->user_id);
        $amount = $data->amount * $data->currency->rate;
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
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'Deposit_create_manager_fee';
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost, 10);
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 10);
            }
            $referral_user = User::findOrFail($user->referral_id);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $transaction_custom_cost;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

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
        user_wallet_increment(0, $data->currency_id, $transaction_global_cost, 9);

        $trans_wallet = get_wallet($user->id, $data->currency_id, 1);

        $trans = new Transaction();
        $trans->trnx = $data->deposit_number;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;
        $trans->amount      = $amount;
        $trans->charge      = $final_chargefee;
        $trans->type        = '+';

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

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
            @mail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return redirect()->back()->with("message", $msg);
      }
}

