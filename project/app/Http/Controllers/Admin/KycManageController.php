<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\KYC;
use App\Models\User;
use App\Models\Charge;
use App\Models\KycForm;
use App\Classes\SumsubKYC;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use App\Models\KycRequest;

class KycManageController extends Controller
{
    public function datatables()
    {
        $datas = User::where('kyc_info','!=',NULL)->OrWhere('kyc_token', '!=', NULL)->orderBy('id','desc')->get();

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

    public function kycdatatables() {
        $datas = KycForm::orderBy('id', 'asc')->get();
        return Datatables::of($datas)
        ->addIndexColumn()
        ->editColumn('status', function(KycForm $data) {
            return $data->status == 1 ? 'Active' : 'Deactive' ;
        })
        ->addColumn('action', function(KycForm $data) {
            $status = 1;
            $status_str = 'Active';
            if($data->status == 1) {
                $status = 0;
                $status_str = "Deactive";
            }

            return '<div class="btn-group mb-1">
            <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.'Actions' .'
            </button>
            <div class="dropdown-menu" x-placement="bottom-start">
                <a href="'.route('admin.manage.kyc.edit', $data->id).'"  class="dropdown-item">'.__("Edit").'</a>
                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.route('admin.kyc.form.delete', $data->id).'">'.__("Delete").'</a>
                <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'.route('admin.manage.kyc.status',[$data->id, $status] ).'">'.__($status_str).'</a>
            </div>
            </div>';

        })
        ->rawColumns(['action'])
        ->toJson(); //--- Returning Json Data To Client Side

    }

    public function index()
    {
        return view('admin.kyc.index');
    }

    public function create_form()
    {
        return view('admin.kyc.create_forms');
    }

    public function store_form(Request $request)
    {
            $data = new KycForm();
            $data->name = $request->title;
            $data->user_type = 1;
            $data->status = 1;
            $data->data = json_encode(array_values($request->form_builder));
            $data->save();

            return redirect()->route('admin.manage.kyc.index')->with('message', 'KYC Form has been created successfully.');
    }

    public function form_status($id, $status) {
        $data = KycForm::findOrFail($id);
        $data->status = $status;
        $data->save();
        return response()->json('Data Updated Successfully.');
    }

    public function edit_form($id) {
        $data = KycForm::findOrFail($id);
        return view('admin.kyc.edit_forms', compact('data'));
    }

    public function update_form(Request $request , $id ) {
        $data = KycForm::findOrFail($id);
        $data->name = $request->title;
        $data->user_type = 1;
        $data->status = $request->status;
        $data->data = json_encode(array_values($request->form_builder));
        $data->save();
        return redirect()->route('admin.manage.kyc.index')->with('message', 'KYC Form has been update successfully.');
    }

    public function add_more_form(Request $request)
    {
        $kycform = KycForm::findOrFail($request->manual_kyc);
        $data = new KycRequest();
        $data->user_id = $request->user_id;
        $data->title = $kycform->name;
        $information = [];
        foreach(json_decode($kycform->data) as $key => $value)
        {
            if($value->type == 1){
                $information[$key]['type'] = 'Input';
            }
            elseif($value->type == 3){
                $information[$key]['type'] = 'Textarea';
            }else{
                $information[$key]['type'] = 'Image';
            }
            $information[$key]['label'] = $value->label;
            $information[$key]['required'] = $value->required;
        }
        $data->kyc_info = json_encode($information, true);
        $data->request_date = date('Y-m-d H:i:s');
        $data->status = 0;
        $data->save();

        return back()->with('message', 'New Kyc Form has been added successfully.');

    }

    // public function index()
    // {
    //     $userType = 'user';
    //     $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();
    //     return view('admin.kyc.user_forms',compact('userType','userForms'));
    // }

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

        return back()->with('message','Form field updated successfully');

    }

    public function deletedField($id)
    {
        KycForm::findOrFail($id)->delete();
        return response()->json('Form field has removed');
    }


    public function kycDetails($id)
    {
        $data['user'] = User::findOrFail($id);
        if ($data['user']->kyc_method == 'auto' && isset($data['user']->kyc_info) != null){
            $folderPath = 'assets/images/';
            $SBObject = new SumsubKYC();
            $app_data = $SBObject->getApplicantData($data['user']->kyc_token);
            $applicantId = $app_data->{'id'};
            $inspectionId = $app_data->inspectionId;
            $app_status = $SBObject->getApplicantStatus($applicantId);
            $requireInformations = [];
            $details = [];
            if($app_status->SELFIE == null && $app_status->IDENTITY == null) {
                return back()->with('warning','This customer did not complete KYC verification step.');
            }
            if($app_status->SELFIE->imageIds) {
                foreach ($app_status->SELFIE->imageIds as $key => $value) {
                    if($value) {
                        $image = $SBObject->getImage($inspectionId, $value);
                        $fileName = uniqid() . '.png';

                        $file = $folderPath . $fileName;

                        file_put_contents($file, $image);
                        $requireInformations['file'][$key] = strtolower($app_status->SELFIE->idDocType);
                        $details[$app_status->SELFIE->idDocType.'_Image_'.($key+1)] = [$fileName,'file'];
                    }
                }
            }
            if($app_status->IDENTITY->imageIds) {
                foreach ($app_status->IDENTITY->imageIds as $key => $value) {
                    if($value) {
                        $image = $SBObject->getImage($inspectionId, $value);
                        $fileName = uniqid() . '.png';

                        $file = $folderPath . $fileName;

                        file_put_contents($file, $image);
                        $requireInformations['file'][count($app_status->SELFIE->imageIds)+$key] = strtolower($app_status->IDENTITY->idDocType);
                        $details[$app_status->IDENTITY->idDocType.'_Image_'.($key+1)] = [$fileName,'file'];
                    }
                }
            }
            $details['ID_TYPE'] = [$app_data->info->idDocs[0]->idDocType ?? '', 'text'];
            $details['Country'] = [$app_data->info->idDocs[0]->country ?? '', 'text'];
            $details['ID_Number'] = [$app_data->info->idDocs[0]->number ?? '', 'text'];
            $data['user']->kyc_info = json_encode($details,true);
            $data['user']->save();
        }
        $data['kycInformations'] = json_decode($data['user']->kyc_info,true);
        return view('admin.kyc.details',$data);
    }

    public function kyc($id1,$id2)
    {
        $user = User::findOrFail($id1);
        $user->kyc_status = $id2;
        $gs = Generalsetting::first();

        if($id2 == 1) { //Approve
            $pre_sections = explode(" , ", $user->section);
            $sectionlist = ['Incoming', 'External Payments', 'Request Money', 'Transactions', 'Payments', 'Payment between accounts', 'Exchange Money'];
            foreach($sectionlist as $key=>$section){
                if (!$user->sectionCheck($section)) {
                    $manualfee = Charge::where('user_id', $id1 )->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    if(!$manualfee) {
                        $manualfee = Charge::where('user_id', 0)->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    }
                    if($manualfee && $manualfee->data->fixed_charge > 0) {
                        $trans = new Transaction();
                        $trans->trnx = str_rand();
                        $trans->user_id     = $id1;
                        $trans->user_type   = 1;
                        $trans->currency_id = defaultCurr();
                        $trans->amount      = 0;
                        $trans_wallet = get_wallet($id1, defaultCurr(), 1);
                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                        $trans->charge      = $manualfee->data->fixed_charge;
                        $trans->type        = '-';
                        $trans->remark      = 'module';
                        $trans->details     = $section.trans(' Section Create');
                        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                        $trans->save();

                        user_wallet_decrement($id1, defaultCurr(), $manualfee->data->fixed_charge, 1);
                        user_wallet_increment(0, defaultCurr(), $manualfee->data->fixed_charge, 9);
                    }
                    array_push($pre_sections, $section);
                }
            }
            $modules = explode(" , ", $user->modules);
            foreach($sectionlist as $key=>$section) {
                if(!$user->moduleCheck($section)) {
                    array_push($modules, $section);
                }
            }
            $user->modules= implode(" , ", $modules);
            $user->section= implode(" , ", $pre_sections);

            // $data = Generalsetting::first();
            // $kyc_modules = explode(" , ", $data ? $data->module_section : []);
            // $user_modules = explode(" , ", $user->section);
            // $new_modules = array_merge($kyc_modules, $user_modules);
            // $new_modules = array_unique($new_modules);
            // $user->modules= implode(" , ", $new_modules);
            // $user->section = implode(" , ", $new_modules);
        }

        $user->update();
        return response()->json('Data Updated Successfully.');
    }

    public function kyc_more($id1,$id2)
    {
        $kycrequest = KycRequest::where('id',$id1)->first();
        $kycrequest->status = $id2;

        $kycrequest->update();
        return response()->json('Data Updated Successfully.');
    }

    public function moreDetails($id)
    {
        $data['kycrequest'] = KycRequest::where('id', $id)->first();
        $data['user'] =  User::findOrFail($data['kycrequest']->user_id);
        $data['kycInformations'] = json_decode($data['kycrequest']->submit_info,true);
        return view('admin.aml.details',$data);
    }

}
