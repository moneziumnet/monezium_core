<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Admin;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;


class InstitutionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = Admin::where('id','!=',1)->where('id','!=',Auth::guard('admin')->user()->id)->orderBy('id');

         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->addColumn('role_id', function(Admin $data) {
                                $role = $data->role_id == 0 ? 'No Role' : $data->staff_role->name;
                                return $role;
                            })
                            ->addColumn('status', function(Admin $data) {
                                $status      = $data->status == 0 ? __('Block') : __('Unblock');
                                $status_sign = $data->status == 0 ? 'danger'   : 'success';

                                    return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.$status .'
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-staff-block',['id1' => $data->id, 'id2' => 1]).'">'.__("Unblock").'</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-staff-block',['id1' => $data->id, 'id2' => 0]).'">'.__("Block").'</a>
                                    </div>
                                    </div>';
                            })

                            ->addColumn('action', function(Admin $data) {

                              return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                '.'Actions' .'
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.institution.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
                                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.institution.delete',$data->id).'">'.__("Delete").'</a>
                              </div>
                            </div>';

                            })
                            ->rawColumns(['action','role_id', 'status'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
  	public function index()
    {
        return view('admin.institution.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.institution.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|unique:admins',
            'photo' => 'required|mimes:jpeg,jpg,png,svg',
            'password'=> 'required',
            'role_id'=> 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
        return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
       //--- Validation Section Ends

        //--- Logic Section
        $data = new Admin();
        $input = $request->all();
        if ($file = $request->file('photo'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            $input['photo'] = $name;
        }

        $input['role_id'] = $request->role_id;
        $input['password'] = bcrypt($request['password']);
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('New Data Added Successfully.').'<a href="'.route('admin.institution.index').'">'.__('View Lists.').'</a>';;

        return response()->json($msg);
        //--- Redirect Section Ends
 }

 public function block($id1,$id2)
 {
     $user = Admin::findOrFail($id1);
     $user->status = $id2;
     $user->update();
     $msg = 'Data Updated Successfully.';
     return response()->json($msg);
 }

    public function edit($id)
    {
        $data = Admin::findOrFail($id);
        return view('admin.institution.edit',compact('data'));
    }

    public function update(Request $request,$id)
    {

        if($id != Auth::guard('admin')->user()->id)
        {
            $rules =
            [
                'photo' => 'mimes:jpeg,jpg,png,svg',
                'email' => 'unique:admins,email,'.$id
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            //--- Validation Section Ends
            $input = $request->all();
            $data = Admin::findOrFail($id);
                if ($file = $request->file('photo'))
                {
                    $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                    $file->move('assets/images/',$name);
                    if($data->photo != null)
                    {
                        if (file_exists(public_path().'/assets/images/'.$data->photo)) {
                            unlink(public_path().'/assets/images/'.$data->photo);
                        }
                    }
                $input['photo'] = $name;
                }
            if($request->password == ''){
                $input['password'] = $data->password;
            }
            else{
                $input['password'] = Hash::make($request->password);
            }
            $data->update($input);
            $msg = 'Data Updated Successfully.'.'<a href="'.route("admin.institution.index").'">View Post Lists</a>';

            return response()->json($msg);
        }
        else{
            $msg = 'You can not change your role.';
            return response()->json($msg);
        }

    }



    //*** GET Request Delete
    public function destroy($id)
    {
    	if($id == 1)
    	{
        return "You don't have access to remove this admin";
    	}
        $data = Admin::findOrFail($id);
        //If Photo Doesn't Exist
        if($data->photo == null){
            $data->delete();
            //--- Redirect Section
            $msg = 'Data Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //If Photo Exist
        @unlink('assets/images/'.$data->photo);
        $data->delete();
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
