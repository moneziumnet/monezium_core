<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CryptoDeposit;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;

class CryptoDepositController extends Controller
{
    public function datatables()
    {
        $datas = CryptoDeposit::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(CryptoDeposit $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(CryptoDeposit $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->name;
                        })
                        ->editColumn('amount', function(CryptoDeposit $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('status', function(CryptoDeposit $data) {
                            if ($data->status == 1) {
                                $status  = __('Completed');
                              } elseif ($data->status == 2) {
                                $status  = __('Rejected');
                              } else {
                                $status  = __('Pending');
                              }

                              if ($data->status == 1) {
                                $status_sign  = 'success';
                              } elseif ($data->status == 2) {
                                $status_sign  = 'danger';
                              } else {
                                $status_sign = 'warning';
                              }

                              return '<div class="btn-group mb-1">
                                                      <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        ' . $status . '
                                                      </button>
                                                      <div class="dropdown-menu" x-placement="bottom-start">
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.deposits.crypto.status', ['id1' => $data->id, 'status' => 1]) . '">' . __("completed") . '</a>
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.deposits.crypto.status', ['id1' => $data->id, 'status' => 2]) . '">' . __("rejected") . '</a>
                                                      </div>
                                                    </div>';
                            })
                        ->editColumn('action', function(DepositBank $data) {
                            // $detail = SubInsBank::where('name', $data->method)->first();
                            // $bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id', $detail->id)->where('currency_id', $data->currency_id)->first();
                            // $detail->address = str_replace(' ', '-', $detail->address);
                            // $detail->name = str_replace(' ', '-', $detail->name);
                            // $doc_url = $data->document ? $data->document : null;
                            // return '<input type="hidden", id="sub_data", value ='.json_encode($detail).'>'.' <a href="javascript:;"   onclick=getDetails('.json_encode($detail).','.json_encode($bankaccount).',"'.$doc_url.'") class="detailsBtn" >
                            // ' . __("Details") . '</a>';
                            return '<a>'. __("Details") .'</a>';
                        })
                        ->rawColumns(['created_at','customer_name','amount','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.cryptodeposit.index');
    }

    public function status($id1,$id2){
        $data = CryptoDeposit::findOrFail($id1);

        if($data->status == 'complete'){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        $user = User::findOrFail($data->user_id);

        user_wallet_increment($user->id, $data->currency_id, $data->amount, 8);



        $trans = new Transaction();
        $trans->trnx = $data->hash;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;
        $trans->amount      = $data->amount;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'Deposit_create';
        $trans->details     = trans('Deposit complete');
        $trans->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
        $trans->save();

        $data->update(['status' => 'complete']);
        $gs = Generalsetting::findOrFail(1);
        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "Deposit",
                'cname' => $user->name,
                'oamount' => $data->amount,
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
            $to = $user->email;
            $subject = " You have deposited successfully.";
            $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
        }

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}

