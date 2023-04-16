<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datatables;
use Auth;

class StaffManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables()
    {
        $datas = Admin::where('id', '!=', auth()->id())->where('role', '=', 'staff')->orderBy('status', 'desc')->get();

         return Datatables::of($datas)
            ->addColumn('name', function(Admin $data) {
                $name = $data->company_name ?? $data->name;
                return $name;
            })
            ->addColumn('status', function(Admin $data) {
                $status      = $data->role == 'staff' ? __('Turn On') : __('Turn Off');
                $status_sign = $data->role == 'staff' ? 'success'   : 'danger';

                    return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        '.$status .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.staff.status',['id' => $data->id, 'status' => 'staff']).'">'.__("Turn On").'</a>
                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.staff.status',['id' => $data->id, 'status' => 'guest']).'">'.__("Turn Off").'</a>
                    </div>
                    </div>';
            })
            ->addColumn('action', function(Admin $data) {
                return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                        <a href="' . route('admin-staff-profilemodule',$data->id) . '"  class="dropdown-item">'.__("Permission").'</a>
                        <a href="' . route('admin.staff.delete',$data->id) . '"  class="dropdown-item">'.__("Delete Staff").'</a>
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
        $user = Admin::findOrFail($id);
        $user->role = $status;
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
            'email'   => 'required|email|unique:admins',
            'password' => 'required||min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $gs = Generalsetting::first();

        $user = new Admin;
        $user->email = $request->email;
        $user->password = bcrypt($request['password']);
        $user->name = trim($request->firstname)." ".trim($request->lastname);
        $user->role = 'staff';
        $user->save();

        
        $msg = __('You have added New Staff Successfully. Please view staff list. ').'<a href="'.route('admin.staff.index').'">'.__('View Lists.').'</a>';

        return response()->json($msg);
    }

    public function profileModules($id)
    {
        $data = Admin::findOrFail($id);
        $data['data'] = $data;
        return view('admin.staff.profilemodules',$data);
    }

    public function destroy($id)
    {
        $data = Admin::findOrFail($id);
        $data->delete();
        $msg = 'Staff has been deleted successfully.';
        return redirect()->back()->with('message',$msg);
    }

    public function moduleupdate(Request $request, $id)
    {
        if ($id != Auth::guard('admin')->user()->id) {
            $input = $request->all();
            $data = Admin::findOrFail($id);
            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
            } else {
                $input['section'] = '';
            }
            $data->section = $input['section'];
            $data->update();
            $msg = 'Data Updated Successfully.';

            return response()->json($msg);
        } else {
            $msg = 'You can not change your role.';
            return response()->json($msg);
        }
    }

    
}

