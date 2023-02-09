<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;

class AdminBeneficiaryController extends Controller
{
    public function datatables($id)
    {
        $datas = Beneficiary::where('user_id', $id)->orderBy('id','desc');

        return Datatables::of($datas)
                        ->addColumn('detail', function (Beneficiary $data) {
                            return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm" onclick="getDetails(event)" id="'.$data->id.'">Details</button>
                            </div>';
                          })
                        ->addColumn('action', function (Beneficiary $data) {
                        return '<div class="btn-group mb-1">
                            <a href="'.route('admin-user-beneficiary-edit', $data->id).'" type="button" class="btn btn-primary btn-sm" >Edit</a>
                        </div>';
                          })
                        ->rawColumns(['detail','action'])
                        ->toJson();
    }

    public function index($id){
        $user = User::findOrFail($id);
        $data['data'] = $user;
        return view('admin.user.profilebeneficiary', $data);
    }

    public function create($id){
        $user = User::findOrFail($id);
        $data['data'] = $user;
        return view('admin.user.beneficiary.create', $data);
    }

    public function store(Request $request){
        $data = new Beneficiary();
        $input = $request->all();

        if($request->type == 'RETAIL') {
            $input['name'] =  trim($request->firstname)." ".trim($request->lastname);
        }
        else {
            $input['name'] =  $request->company_name;
        }
        $data->fill($input)->save();

        return redirect()->route('admin-user-beneficiary', $data->user_id)->with('message','Beneficiary Added Successfully');
    }

    public function edit($id){
        $data['beneficiary'] = Beneficiary::where('id', $id)->first();
        $user = User::findOrFail($data['beneficiary']->user_id);
        $data['data'] = $user;
        return view('admin.user.beneficiary.edit', $data);
    }

    public function update(Request $request, $id) {

        $data = Beneficiary::findOrFail($id);
        $input = $request->all();

        if($request->type == 'RETAIL') {
            $input['name'] =  trim($request->firstname)." ".trim($request->lastname);
        }
        else {
            $input['name'] =  $request->company_name;
        }
        $data->fill($input)->update();
        return redirect()->route('admin-user-beneficiary', $data->user_id)->with('message','Beneficiary has been updated successfully');
    }

    public function details($id){
        $data['beneficiary'] = Beneficiary::where('id', $id)->first();
        return view('admin.user.beneficiary.details', $data);
    }

}
