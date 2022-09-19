<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IcoToken;
use Datatables;

class ICOController extends Controller
{
    public function datatables()
    {
        $datas = IcoToken::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('name',function(IcoToken $data){
                            return str_dis($data->name);
                        })
                        ->editColumn('user_name',function(IcoToken $data){
                            return str_dis($data->user->name);
                        })
                        ->editColumn('price', function(IcoToken $data) {
                            return $data->price;
                        })
                        ->editColumn('code', function(IcoToken $data) {
                            return $data->currency->code;
                        })
                        ->editColumn('symbol', function(IcoToken $data) {
                            return $data->currency->symbol;
                        })
                        ->editColumn('total_supply', function(IcoToken $data) {
                            return $data->total_supply;
                        })
                        ->editColumn('end_date', function(IcoToken $data) {
                            $date = date('d-m-Y',strtotime($data->end_date));
                            return $date;
                        })
                        ->editColumn('status', function(IcoToken $data) {
                              if ($data->status == 1) {
                                $status  = __('Approved');
                              } else {
                                $status  = __('Pending');
                              }

                              if ($data->status == 1) {
                                $status_sign  = 'success';
                              } else {
                                $status_sign = 'warning';
                              }

                              return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ' . $status . '
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.ico.status', ['id' => $data->id, 'status' => 1]) . '">' . __("approve") . '</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.ico.status', ['id' => $data->id, 'status' => 0]) . '">' . __("disable") . '</a>
                                        </div>
                                    </div>';
                            })
                        ->editColumn('action', function(IcoToken $data) {
                            return '<a href="javascript:;" onclick=getDetails('.$data->id.') class="detailsBtn">' . __("Details") . '</a>';
                        })
                        ->rawColumns(['name','user_name','price','code','symbol','total_supply','end_date','status','action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.ico.index');
    }

    public function details($id){
        $data['item'] = IcoToken::findOrFail($id);
        return view('admin.ico.detail', $data);
    }

    public function status($id,$status){
        $data = IcoToken::findOrFail($id);

        if($data->status == 0 && $status == 0 ){
          $msg = 'ICO Token already disabled';
          return response()->json($msg);
        }

        if($data->status == 1 && $status == 1){
            $msg = 'ICO Token already approved';
            return response()->json($msg);
        }

        $data->status = $status;
        $data->save();

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
}