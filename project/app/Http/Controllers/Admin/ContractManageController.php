<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Contract;
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
                            <a href="#" class="dropdown-item" >'.__("View AoA").'</a>
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
}

