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
                            <a href="' . route('admin.contract.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
                            <a href="#" class="dropdown-item" >'.__("View").'</a>
                            <a href="#" class="dropdown-item" >'.__("Delete").'</a>
                            <a href="#" class="dropdown-item" >'.__("Manage AoA").'</a>
                            </div>
                        </div>';
                        })
                        ->rawColumns(['title','description','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.contract.index');
    }

    public function create() {
        return view('admin.contract.create');
    }

    public function store(Request $request) {
        $rules = ['title' => 'required'];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = new Contract();
        $input = $request->all();
        $data->fill($input)->save();

        $msg = __('New Contract Added Successfully.').' '.'<a href="'.route("admin.contract.management").'">'.__('View Lists.').'</a>';
        return response()->json($msg);
    }

    public function edit($id) {
        $data = Contract::findOrFail($id);
        return view('admin.contract.edit', compact('data'));
    }

    public function update(Request $request, $id) {
        $rules = ['title' => 'required'];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = Contract::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        $msg = __('A Contract Updated Successfully.').' '.'<a href="'.route("admin.contract.management").'">'.__('View Lists.').'</a>';
        return response()->json($msg);
    }


}

