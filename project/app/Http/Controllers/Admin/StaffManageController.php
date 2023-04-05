<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datatables;

class StaffManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables()
    {
        $datas = Staff::orderBy('status', 'desc')->get();

         return Datatables::of($datas)
            ->addColumn('name', function(Staff $data) {
                $name = $data->company_name ?? $data->name;
                return $name;
            })
            ->addColumn('status', function(Staff $data) {
                $status      = $data->status == 1 ? __('Turn On') : __('Turn Off');
                $status_sign = $data->status == 1 ? 'success'   : 'danger';

                    return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        '.$status .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.staff.status',['id' => $data->id, 'status' => 1]).'">'.__("Turn On").'</a>
                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.staff.status',['id' => $data->id, 'status' => 0]).'">'.__("Turn Off").'</a>
                    </div>
                    </div>';
            })

            ->rawColumns(['name','action','status'])
            ->toJson();
    }

    public function index(){
        return view('admin.staff.index');
    }

    public function staff_status($id,$status)
    {
        $user = Staff::findOrFail($id);
        $user->status = $status;
        $user->update();
        $msg = 'Staff Status Updated Successfully.';
        return response()->json($msg);
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {

        $rules = [
            'email'   => 'required|email|unique:staffs',
            'password' => 'required||min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $gs = Generalsetting::first();

        $user = new Staff;
        $user->email = $request->email;
        $user->password = bcrypt($request['password']);
        $user->name = trim($request->firstname)." ".trim($request->lastname);
        $user->save();

        
        $msg = __('You have added New Staff Successfully. Please view staff list. ').'<a href="'.route('admin.staff.index').'">'.__('View Lists.').'</a>';

        return response()->json($msg);
    }

    
}

