<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractAoa;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datatables;

class ContractManageController extends Controller
{
    public function datatables()
    {
        $datas = Contract::orderBy('id','desc');

        return Datatables::of($datas)
                        ->addColumn('title',function(Contract $data){
                            return $data->title;
                        })
                        ->addColumn('description',function(Contract $data){
                            if ( strlen($data->description) > 20 ) {
                                return htmlentities(substr($data->description, 0, 10)).' ...';
                            }
                            else {
                                return htmlentities($data->description);
                            }
                        })
                        ->editColumn('image_path', function(Contract $data){
                            if (isset($data->image_path)) {
                                return '<a href ="'.asset('assets/images/'.$data->image_path).'" attributes-list download > Download E-Sign </a>';
                            }
                            else {
                                return 'Not Signed';
                            }
                        })
                        ->editColumn('status', function(Contract $data) {
                            $status = $data->status == 0 ? '<span class="badge badge-warning">Not Signed</span>' : '<span class="badge badge-success">Signed</span>';
                            return $status;
                        })
                        ->editColumn('action', function(Contract $data) {
                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            '.'Actions' .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                            <a href="' . route('admin.contract.view',$data->id) . '"  class="dropdown-item">'.__("View").'</a>
                            <a href="'.route('admin.aoa.index', $data->id).'" class="dropdown-item" >'.__("View AoA").'</a>
                            </div>
                        </div>';
                        })
                        ->rawColumns(['title','description','status', 'action', 'image_path'])
                        ->toJson();
    }

    public function index(){
        return view('admin.contract.index');
    }

    public function view($id) {
        $data = Contract::findOrFail($id);
        return view('admin.contract.view', compact('data'));
    }

    public function aoa_index($id) {
        return view('admin.aoa.index', compact('id'));
    }

    public function aoa_datatables($id)
    {
        $datas = ContractAoa::where('contract_id', $id)->orderBy('id','desc');

        return Datatables::of($datas)
                        ->addColumn('title',function(ContractAoa $data){
                            return $data->title;
                        })
                        ->addColumn('description',function(ContractAoa $data){
                            if ( strlen($data->description) > 20 ) {
                                return htmlentities(substr($data->description, 0, 10)).' ...';
                            }
                            else {
                                return htmlentities($data->description);
                            }
                        })
                        ->editColumn('contracter_image_path', function(ContractAoa $data){
                            if (isset($data->contracter_image_path)) {
                                return '<a href ="'.asset('assets/images/'.$data->contracter_image_path).'" attributes-list download > Download E-Sign </a>';
                            }
                            else {
                                return 'Not Signed';
                            }
                        })
                        ->editColumn('customer_image_path', function(ContractAoa $data){
                            if (isset($data->customer_image_path)) {
                                return '<a href ="'.asset('assets/images/'.$data->customer_image_path).'" attributes-list download > Download E-Sign </a>';
                            }
                            else {
                                return 'Not Signed';
                            }
                        })
                        ->editColumn('status', function(ContractAoa $data) {
                            $status = $data->status == 0 ? '<span class="badge badge-warning">Not Signed</span>' : '<span class="badge badge-success">Signed</span>';
                            return $status;
                        })
                        ->editColumn('action', function(ContractAoa $data) {
                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            '.'Actions' .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                            <a href="' . route('admin.aoa.view',$data->id) . '"  class="dropdown-item">'.__("View").'</a>
                            </div>
                        </div>';
                        })
                        ->rawColumns(['title','description','status', 'action', 'contracter_image_path', 'customer_image_path'])
                        ->toJson();
    }

    public function aoa_view($id) {
        $data = ContractAoa::findOrFail($id);
        return view('admin.aoa.view', compact('data'));
    }
}

