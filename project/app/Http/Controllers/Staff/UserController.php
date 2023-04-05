<?php

namespace App\Http\Controllers\Staff;

use Datatables;
use App\Models\User;
use App\Models\Generalsetting;
use App\Models\Currency;
use App\Models\KycForm;
use App\Models\KycRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:staff');
    }

    public function datatables()
    {
            $datas = User::orderBy('id','desc');

            return Datatables::of($datas)
            ->addColumn('name', function(User $data) {
                $name = $data->company_name ?? $data->name;
                return $name;
            })
            ->editColumn('balance', function(User $data) {
                $currency = Currency::findOrFail(defaultCurr());
                return '<div clase="text-right">'.$currency->symbol.amount(userBalance($data->id), $currency->type, 2).'</div>';
            })
            ->addColumn('action', function(User $data) {
                return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="'.route('staff-user-profile', $data->id).'"  class="dropdown-item">'.__("Profile").'</a>
                    </div>
                </div>';
            })

            ->rawColumns(['name','action','balance'])
            ->toJson();
    }

    public function index()
    {
        return view('staff.user.index');
    }

    public function profileInfo($id)
    {
        $user = User::findOrFail($id);
        $data['data'] = $user;
        return view('staff.user.profile',$data);
    }

    public function profilekycinfo($id) {
        $data['data'] = User::findOrFail($id);
        $data['kycforms'] = KycForm::where('status', 1)->get();
        $data['url'] = route('staff.kyc.details',$id);
        if($data['data']->kyc_status == 1){
            $status  = __('Approved');
            }elseif($data['data']->kyc_status == 2){
            $status  = __('Rejected');
            }else{
            $status =  __('Pending');
            }

            if($data['data']->kyc_status == 1){
            $status_sign  = 'success';
            }elseif($data['data']->kyc_status == 2){
            $status_sign  = 'danger';
            }else{
            $status_sign = 'warning';
            }
        $data['status'] = $status;
        $data['status_sign'] = $status_sign;
        return view('staff.user.profilekycinfo', $data);
    }

    public function additionkycdatatables($id)
    {
        $datas = KycRequest::where('user_id', $id)->get();

        return Datatables::of($datas)
            ->addColumn('action', function(KycRequest $data) {
                if($data->status == 1){
                $status  = __('Approved');
                }elseif($data->status == 2){
                $status  = __('Rejected');
                }else{
                $status =  __('Pending');
                }

                if($data->status == 1){
                $status_sign  = 'success';
                }elseif($data->status == 2){
                $status_sign  = 'danger';
                }else{
                $status_sign = 'warning';
                }

                return '<div class="btn-group mb-1">
                <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.$status .'
                </button>
                <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal1" class="dropdown-item" data-href="'. route('staff.more.user.kyc',['id1' => $data->id, 'id2' => 1]).'">'.__("Approve").'</a>
                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal1" class="dropdown-item" data-href="'. route('staff.more.user.kyc',['id1' => $data->id, 'id2' => 2 ]).'">'.__("Reject").'</a>
                </div>
                </div>';

            })
            ->addColumn('detail', function(KycRequest $data) {
                $url = route('staff.more.user.kyc.details',$data->id);
                return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="' .$url. '"  class="dropdown-item">'.__("Details").'</a>
                    </div>
                </div>';
            })
            ->rawColumns(['action', 'detail'])
            ->toJson();
    }

    public function KycForm($id)
    {
        $user=User::findOrFail($id);
        return view('staff.user.kyc_more_forms', compact('user'));
    }

    public function StoreKycForm(Request $request)
    {
        $data = new KycRequest();
        $input = $request->all();
        $input['kyc_info'] = json_encode(array_values($request->form_builder));
        $input['request_date'] = date('Y-m-d H:i:s');
        $data->fill($input)->save();

        $msg = __('New Data Added Successfully.').' '.'<a href="'.route("staff.user.kycinfo", $request->user_id).'">'.__('View Lists.').'</a>';
        return response()->json($msg);
    }


}
