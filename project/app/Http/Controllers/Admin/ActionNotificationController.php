<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionNotification;
use App\Models\IcoToken;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;

class ActionNotificationController extends Controller
{
    public function datatables()
    {
        $datas = ActionNotification::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('user_name',function(ActionNotification $data){
                            $user = User::findOrFail($data->user_id);
                            return $user->company_name ?? $user->name;
                        })
                        ->editColumn('description',function(ActionNotification $data){
                            return nl2br($data->description);
                        })
                        ->editColumn('status', function(ActionNotification $data) {
                              if ($data->status == 1) {
                                $status  = __('Read');
                              } else {
                                $status  = __('Unread');
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
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item action_notify" data-status="1" data-id="'.$data->id.'" data-url="' . route('admin.actionnotification.status') . '">' . __("Read") . '</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item action_notify" data-status="0"  data-id="'.$data->id.'" data-url="' . route('admin.actionnotification.status') . '">' . __("Unread") . '</a>
                                        </div>
                                    </div>';
                            })
                        ->rawColumns(['user_name','description','status'])
                        ->toJson();
    }

    public function index(){
        $data = ActionNotification::where('status', '0')->update(['status' => '1']);
        return view('admin.actionnotification.index');
    }


    public function status(Request $request){
        $data = ActionNotification::findOrFail($request->notify_id);
        if($data->status == 0 && $request->notify_status == 0 ){
          $msg = 'Please read this notification';
          return redirect()->back()->with('warning', $msg);
        }

        $data->status = $request->notify_status;
        $data->save();
        return redirect($data->url)->with('message', 'you have checked successfully.');
      }
}
