<?php

namespace App\Http\Controllers\Staff;

use Datatables;
use App\Models\KYC;
use App\Models\User;
use App\Models\Charge;
use App\Models\KycForm;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\KycRequest;
use App\Classes\SumsubKYC;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class KycManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:staff');
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
        return view('staff.kyc.details',$data);
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
        return view('staff.aml.details',$data);
    }

}
