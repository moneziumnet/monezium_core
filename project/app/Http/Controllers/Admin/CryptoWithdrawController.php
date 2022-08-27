<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CryptoWithdraw;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;

class CryptoWithdrawController extends Controller
{
    public function datatables()
    {
        $datas = CryptoWithdraw::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('created_at', function(CryptoWithdraw $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(CryptoWithdraw $data){
                            $data = User::where('id',$data->user_id)->first();
                            return $data->name;
                        })
                        ->editColumn('amount', function(CryptoWithdraw $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('status', function(CryptoWithdraw $data) {
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
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.withdraws.crypto.status', ['id1' => $data->id, 'id2' => 1]) . '">' . __("completed") . '</a>
                                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.withdraws.crypto.status', ['id1' => $data->id, 'id2' => 2]) . '">' . __("rejected") . '</a>
                                                      </div>
                                                    </div>';
                            })
                        ->editColumn('action', function(CryptoWithdraw $data) {
                            return '<input type="hidden", id="sub_data", value ='.json_encode($data).'>'.' <a href="javascript:;"   onclick=getDetails('.json_encode($data).') class="detailsBtn" >
                            ' . __("Details") . '</a>';
                        })
                        ->rawColumns(['created_at','customer_name','amount','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.cryptowithdraw.index');
    }

    public function status($id1,$id2){
        $data = CryptoWithdraw::findOrFail($id1);

        if($data->status == 1){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        $user = User::findOrFail($data->user_id);

        // user_wallet_increment($user->id, $data->currency_id, $data->amount, 8);



        // $trans = new Transaction();
        // $trans->trnx = $data->hash;
        // $trans->user_id     = $user->id;
        // $trans->user_type   = 1;
        // $trans->currency_id = $data->currency_id;
        // $trans->amount      = $data->amount;
        // $trans->charge      = 0;
        // $trans->type        = '+';
        // $trans->remark      = 'Deposit_create';
        // $trans->details     = trans('Deposit complete');
        // $trans->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
        // $trans->save();
        $data->status = $id2;
        $data->update();
        $gs = Generalsetting::findOrFail(1);
        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "Withdraw",
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
            $subject = " You have withdrawed successfully.";
            $msg = "Hello ".$user->name."!\nYou have withdrawed successfully.\nThank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
        }

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }

    public function edit($id) {
        $data['withdraw'] = CryptoWithdraw::findOrFail($id);
        return view('admin.cryptowithdraw.edit', $data);
    }

    public function update(Request $request, $id) {
        $data = CryptoWithdraw::findOrFail($id);
        $data->hash = $request->hash;
        $data->update();
        return response()->json('You have added hash value successfully. '.'<a href="'.route('admin.withdraws.crypto.index').'"> '.__('View Lists.').'</a>');

    }
}

