<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\KYC;
use App\Models\KycForm;
use App\Models\User;
use Datatables;

class KycManageController extends Controller
{
    public function datatables()
    {
        $datas = User::where('kyc_info','!=',NULL)->OrWhere('kyc_token', '!=', NULL)->orderBy('id','desc');

        return Datatables::of($datas)
                            ->addColumn('action', function(User $data) {
                                $url = route('admin.kyc.details',$data->id);
                                return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.'Actions' .'
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' .$url. '"  class="dropdown-item">'.__("Details").'</a>
                                    </div>
                                </div>';
                            })
                            ->editColumn('kyc_method', function(User $data) {
                                return strtoupper($data->kyc_method);
                            })


                           ->addColumn('kyc', function(User $data) {
                               if($data->kyc_status == 1){
                                $status  = __('Approved');
                               }elseif($data->kyc_status == 2){
                                $status  = __('Rejected');
                               }else{
                                $status =  __('Pending');
                               }

                               if($data->kyc_status == 1){
                                $status_sign  = 'success';
                               }elseif($data->kyc_status == 2){
                                $status_sign  = 'danger';
                               }else{
                                $status_sign = 'warning';
                               }

                                return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.$status .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.user.kyc',['id1' => $data->id, 'id2' => 1]).'">'.__("Approve").'</a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.user.kyc',['id1' => $data->id, 'id2' => 2 ]).'">'.__("Reject").'</a>
                                </div>
                                </div>';

                            })
                            ->rawColumns(['action','status','kyc'])
                            ->toJson();
    }

    public function kycInfo($userType)
    {
        return view('admin.kyc.kyc_info');
    }

    public function index()
    {
        $userType = 'user';
        $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();
        return view('admin.kyc.user_forms',compact('userType','userForms'));
    }

    public function module(){
        $data = Generalsetting::first();
        return view('admin.user.modules',compact('data'));
    }

    public function userKycForm($userType)
    {
        if($userType == 'user' || $userType == 'merchant'){
            $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();
            return view('admin.kyc.user_forms',compact('userType','userForms'));
        }
        abort(404);
    }


    public function kycForm(Request $request)
    {
       $request->validate([
           'type'=> 'required|in:1,2,3',
           'label' => 'required',
           'required' => 'required'
       ]
       );

       $kyc = new KycForm();
       $kyc->user_type = $request->user_type;
       $kyc->type      = $request->type;
       $kyc->label     = $request->label;
       $kyc->name      = Str::slug($request->label,'_');
       $kyc->required  = $request->required;
       $kyc->save();

       return back()->with('success','Form field added successfully');
    }

    public function removeField($id)
    {
        KycForm::findOrFail($id)->delete();
        $notify[]=['success','Field has been removed'];
        return back()->withNotify($notify);
    }

    public function editField($id)
    {
        $page_title = 'Edit Fields';
        $field = KycForm::findOrFail($id);
        return view('admin.category.editFields',compact('page_title','field'));
    }

    public function kycFormUpdate(Request $request)
    {
        $request->validate([
            'type'=> 'required|in:1,2,3',
            'label' => 'required',
            'required' => 'required'
        ]
        );

        $kyc            = KycForm::findOrFail($request->id);
        $kyc->user_type = $request->user_type;
        $kyc->type      = $request->type;
        $kyc->label     = $request->label;
        $kyc->name      = Str::slug($request->label,'_');
        $kyc->required  = $request->required;
        $kyc->save();

        return back()->with('success','Form field updated successfully');

    }

    public function deletedField(Request $request)
    {
        KycForm::findOrFail($request->id)->delete();
        return back()->with('success','Form field has removed');
    }


    public function kycDetails($id)
    {
        $data['user'] = User::findOrFail($id);
        if ($data['user']->kyc_method == 'auto'){
            return back()->with('warning', 'You can not see the details of this user because this user\'s kyc method is "auto"'  );
        }
        $data['kycInformations'] = json_decode($data['user']->kyc_info,true);
        return view('admin.kyc.details',$data);
    }

    public function kyc($id1,$id2)
    {
        $user = User::findOrFail($id1);
        $user->kyc_status = $id2;
        
        if($id2 == 1) { //Approve
            $data = Generalsetting::first();
            $kyc_modules = explode(" , ", $data ? $data->module_section : []);
            $user_modules = explode(" , ", $user->section);
            
            $new_modules = array_merge($kyc_modules, $user_modules, ['Transactions']);
            $new_modules = array_unique($new_modules);
            $user->section = implode(" , ", $new_modules);
        }

        $user->update();
        return response()->json('Data Updated Successfully.');
    }

}