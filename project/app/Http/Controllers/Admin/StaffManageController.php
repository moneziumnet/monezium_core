<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
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
        $datas = User::orderByRaw("CASE WHEN user_type LIKE '%5%' THEN 0 ELSE 1 END")
        ->orderByRaw("ISNULL(user_type)")
        ->orderBy('user_type', 'ASC')
        ->get();

         return Datatables::of($datas)
            ->addColumn('name', function(User $data) {
                $name = $data->company_name ?? $data->name;
                return $name;
            })
            ->addColumn('action', function(User $data) {
                return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="' . route('admin-user-profile',$data->id) . '"  class="dropdown-item">'.__("Profile").'</a>
                    </div>
                </div>';
            })

            ->addColumn('status', function(User $data) {
                $status      = check_user_type_by_id(5, $data->id) ? __('Turn On') : __('Turn Off');
                $status_sign = check_user_type_by_id(5, $data->id) ? 'success'   : 'danger';

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
        $user = User::findOrFail($id);
        if($status && !check_user_type_by_id(5, $id)) {
            $user_type = explode(',',  $user->user_type);
            array_push($user_type, 5);
            $user->user_type = implode(',', $user_type);
        }
        elseif(!$status && check_user_type_by_id(5, $id)) {
            $user_type = explode(',',  $user->user_type);
            $index = array_search("5", $user_type); 
            if ($index !== false) {
                unset($user_type[$index]); 
                $user->user_type = implode(',', $user_type);
            }
        }
        $user->update();
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }

    
}

