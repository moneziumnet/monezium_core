<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\User;
use App\Models\wallet;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;

use App\Models\CryptoWithdraw;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use DB;

class CryptoWithdrawController extends Controller
{
    public function datatables()
    {
        $datas = CryptoWithdraw::orderBy('id','desc')->get();

        return Datatables::of($datas)
                        ->editColumn('created_at', function(CryptoWithdraw $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->addColumn('customer_name',function(CryptoWithdraw $data){
                            $data = User::where('id',$data->user_id)->first();
                            return str_dis($data->company_name ?? $data->name);
                        })
                        ->editColumn('crypto_address',function(CryptoWithdraw $data){
                            return str_dis(Get_Wallet_Address($data->user_id, $data->currency_id));
                        })
                        ->editColumn('sender_address',function(CryptoWithdraw $data){
                            return str_dis($data->sender_address);
                        })
                        ->editColumn('amount', function(CryptoWithdraw $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('action', function(CryptoWithdraw $data) {
                            return '<input type="hidden", id="sub_data", value ='.json_encode($data).'>'.' <a href="javascript:;"   onclick=getDetails('.json_encode($data).',"'.Get_Wallet_Address($data->user_id, $data->currency_id).'") class="detailsBtn" >
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
        $gs = Generalsetting::findOrFail(1);
        if($data->status == 1){
          $msg = 'Deposits already completed';
          return response()->json($msg);
        }

        if($data->status == 2){
            $msg = 'Deposits already rejected';
            return response()->json($msg);
          }

        $user = User::findOrFail($data->user_id);

        $data->status = $id2;
        $data->update();
        $currency = Currency::findOrFail($data->currency_id);

        $crypto_rate = getRate($currency);

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

