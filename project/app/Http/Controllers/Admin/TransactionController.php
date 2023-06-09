<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Datatables;

class TransactionController extends Controller
{

    public function datatables()
    {
        $datas = Transaction::orderBy('created_at','desc')->get();

        return Datatables::of($datas)
                        ->editColumn('amount', function(Transaction $data) {
                            $currency = Currency::whereId($data->currency_id)->first();
                            return $data->type.amount($data->amount,$currency->type,2).$currency->code;
                        })
                        ->editColumn('created_at', function(Transaction $data) {
                            $date = date('d-M-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->editColumn('sender', function(Transaction $data) {
                            $details = json_decode(str_replace(array("\r", "\n"), array('\r', '\n'), $data->data));
                            return ucwords($details->sender ?? "");
                        })
                        ->editColumn('receiver', function(Transaction $data) {
                            $details = json_decode(str_replace(array("\r", "\n"), array('\r', '\n'), $data->data));
                            return str_dis(ucwords($details->receiver ?? ""));
                        })
                        ->editColumn('remark', function(Transaction $data) {
                            return ucwords(str_replace('_',' ',$data->remark));
                        })
                        ->editColumn('trnx', function(Transaction $data) {
                            $trnx = $data->trnx;
                            return $trnx;
                        })
                        ->editColumn('charge', function(Transaction $data) {
                            $currency = Currency::whereId($data->currency_id)->first();
                            return '-'.amount($data->charge,$currency->type,2).$currency->code;
                        })
                        ->addColumn('action', function (Transaction $data) {
                            return ' <a href="javascript:;"  data-href="" onclick="getDetails('.$data->id.')" class="detailsBtn" >
                            ' . __("Details") . '</a>';
                        })

                        ->rawColumns(['action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.transaction.index');
    }
}
