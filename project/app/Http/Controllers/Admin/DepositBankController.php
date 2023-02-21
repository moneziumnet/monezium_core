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
use App\Models\CampaignDonation;
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
                    $webhook_request = WebhookRequest::where('reference', 'LIKE', '%'.$reference)->where('is_pay_in', true)->first();
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
            ->editColumn('deposit_number', function(DepositBank $data) {
                $send_info = WebhookRequest::where('reference', 'LIKE', '%'.$data->deposit_number)->with('currency')->first();
                $deposit_no = $data->deposit_number;
                if ($send_info) {
                    $deposit_no = $send_info->transaction_id;
                }
                return $deposit_no;
            })
            ->addColumn('customer_name',function(DepositBank $data){
                $data = User::where('id',$data->user_id)->first();
                return $data->company_name ?? $data->name;
            })
            ->addColumn('customer_email',function(DepositBank $data){
                $data = User::where('id',$data->user_id)->first();
                return $data->email;
            })
            ->editColumn('amount', function(DepositBank $data) {
                return $data->currency->symbol.round($data->amount);
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
                $send_info = WebhookRequest::where('transaction_id', 'LIKE', '%'.$data->deposit_number)->orWhere('reference', 'LIKE', '%'.$data->deposit_number)->with('currency')->first();


                if(!$detail) {
                    $user_info = User::find($data->user_id);
                    return '<div class="btn-group mb-1">
                            <a href="javascript:;"
                                data-sendinfo = \''.json_encode($send_info).'\'
                                data-number="'.$data->deposit_number.'"
                                data-status="'.$data->status.'"
                                data-description="'.($data->details ?? $send_info->reference).'"
                                data-userinfo=\'{"name":"'.($user_info->company_name ?? $user_info->name ).'","address":"'.($user_info->company_address ?? $user_info->address ).'"}\'
                                data-complete-url="'.route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'complete']).'"
                                data-reject-url="'.route('admin.deposits.bank.status',['id1' => $data->id, 'id2' => 'reject']).'"
                                onclick=getDetails(event)
                                class="btn btn-sm btn-primary detailsBtn">' . __("Details") . '</a>
                        </div>';
                }
                if($detail->hasGateway()){
                    @$bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id', $detail->id)->where('currency_id', $data->currency_id)->with('user')->first();
                } else {
                    @$bankaccount = BankPoolAccount::where('bank_id', $detail->id)->where('currency_id', $data->currency_id)->first();
                }
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
            ->rawColumns(['created_at','customer_name','customer_email','amount','status', 'deposit_number', 'action'])
            ->toJson();
    }

    public function index(){
        return view('admin.depositbank.index');
    }

    public function status($id1,$id2){
        $data = DepositBank::findOrFail($id1);
        $gs = Generalsetting::findOrFail(1);

        if($data->status == 'complete' || $data->status == 'reject'){
          $msg = $data->status == 'complete'
            ? 'Deposits already completed'
            : 'Deposits already rejected';
          return redirect()->back()->with("error", $msg);
        }

        $webhook_request = WebhookRequest::where('reference', 'LIKE', '%'.$data->deposit_number)->orWhere('transaction_id', $data->deposit_number)->first();
        $sender_name = $gs->disqus;
        if($webhook_request) {
            $data->amount = $webhook_request->amount;
            $sender_name = $webhook_request->sender_name;
        }
        $data->status = $id2;
        $data->save();
        if($id2 == 'reject') {
            $msg = 'Data Updated Successfully.';
            return redirect()->back()->with("message", $msg);
        }

        $user = User::findOrFail($data->user_id);
        $rate =  getRate($data->currency);
        $amount = $data->amount / $rate;
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
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 6);
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'Deposit_create_manager_fee';
                user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 10);
                $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 10);
            }
            $referral_user = User::findOrFail($user->referral_id);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $transaction_custom_cost*$rate;

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Deposit complete');

            $trans->data        = '{"sender":"'.$sender_name.'", "receiver":"'.($referral_user->company_name ?? $referral_user->name).'", "description": "'.$data->details.' / '.$data->deposit_number.'"}';
            $trans->save();
        }
        $final_chargefee = $transaction_global_cost + $transaction_custom_cost;
        $final_amount = $amount - $final_chargefee;

        user_wallet_increment($user->id, $data->currency_id, $final_amount*$rate, 1);
        user_wallet_increment(0, $data->currency_id, $transaction_global_cost*$rate, 9);

        $trans_wallet = get_wallet($user->id, $data->currency_id, 1);

        $trans = new Transaction();
        $trans->trnx = $data->deposit_number;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;
        $trans->amount      = $data->amount;
        $trans->charge      = $final_chargefee*$rate;
        $trans->type        = '+';

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trans->remark      = 'Deposit_create';
        $trans->details     = trans('Deposit complete');

        $trans->data        = '{"sender":"'.$sender_name.'", "receiver":"'.($user->company_name ?? $user->name).'", "description": "'.$data->details.' / '.$data->deposit_number.'"}';
        $trans->save();

        $data->update(['status' => 'complete']);
        $campaign = CampaignDonation::where('payment', 'bank_pay-'.$data->deposit_number)->first();

        if ($campaign) {
            $campaign->amount = $final_amount*$rate;
            $campaign->status = 1;
            $campaign->update();
        }

            $to = $user->email;
            $subject = " You have deposited successfully.";
            $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            @sendMail($to,$subject,$msg,$headers);

        $msg = 'Data Updated Successfully.';
        return redirect()->back()->with("message", $msg);
      }
}

