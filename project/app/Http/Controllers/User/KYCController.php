<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\KycForm;
use App\Models\Product;
use App\Models\Charge;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Classes\SumsubKYC;

class KYCController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['onlineSelfie', 'takeOnlineSelfie']]);
    }

    public function kycform()
    {
        if (auth()->user()->kyc_status != 1)
        {
            if (auth()->user()->kyc_status != 3)
            {
                $userType = 'user';
                $user = User::findOrFail(auth()->id());
                $userForms = KycForm::where('id', $user->manual_kyc)->first();
                $token = '';
                if($user->kyc_method == 'auto') {
                    if($user->kyc_token) {

                        $levelName = 'basic-kyc-level';
                        $externalUserId = $user->kyc_token;
                        $testObject = new SumsubKYC();
                        $accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
                        $token = json_decode($accessTokenStr)->token;
                    }
                    else {
                        $externalUserId = uniqid();
                        $levelName = 'basic-kyc-level';

                        $testObject = new SumsubKYC();
                        $applicantId = $testObject->createApplicant($externalUserId, $levelName);

                        $applicantStatusStr = $testObject->getApplicantStatus($applicantId);

                        $accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
                        $token = json_decode($accessTokenStr)->token;
                        $user->kyc_token = $externalUserId;
                        $user->save();
                    }
                }
                return view('user.kyc.index',compact('userType','userForms', 'token'));
            }else{
                return redirect()->route('user.dashboard')->with('unsuccess','You have submitted kyc for verification.');
            }
        }

    }

    public function onlineSelfie($id){
        $user_id = decrypt($id);
        return view('user.kyc.selfie',compact('user_id'));
    }

    public function image_save($img) {
        $folderPath = 'assets/images/';

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);
        $fileName = uniqid() . '.png';

        $file = $folderPath . $fileName;

        file_put_contents($file, $image_base64);
        return $fileName;
    }

    public function takeOnlineSelfie(Request $request){
        /** Direct Photo upload**/
        $img = $request->image;
        $filename = $this->image_save($img) ?? '';
        $img = $request->image_front;
        $front_filename = $this->image_save($img) ?? '';
        $img = $request->image_back;
        $back_filename = $this->image_save($img) ?? '';


        $user = User::findOrFail($request->user_id);
        $user->kyc_photo = $filename.','.$front_filename.','.$back_filename;
        $user->save();

        return back()->with('success', 'KYC submitted successfully');
    }

    public function sendSelfieLink(){
        $user = auth()->user();
        $gs = Generalsetting::first();
        $to = $user->email;
        $subject = " Online Selfie Link";
        $msg = "Hello ".$user->name."!\nThis is the link of online Selfie for you.\nLink is \n".url('/user/kyc-take-selfie')." \n Thank you.";
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        sendMail($to,$subject,$msg,$headers);
    }

    public function kyc(Request $request){
        $userType = 'user';

        $user = auth()->user();
        $userForms = KycForm::where('id', $user->manual_kyc)->first();
        $gs = Generalsetting::first();
        $route = route('user.kyc.selfie',encrypt($user->id));
        $to = $user->email;
        $subject = " Online Selfie Link";
        $msg = "Hello ".$user->name."!\nThis is the link of online Selfie for you.\nLink is \n".$route." \n Thank you.";
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        sendMail($to,$subject,$msg,$headers);

        $requireInformations = [];
        if($userForms){
            foreach(json_decode($userForms->data) as $key=>$value){
                if($value->type == 1){
                    $requireInformations['text'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }
                elseif($value->type == 3){
                    $requireInformations['textarea'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }else{
                    $requireInformations['file'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }
            }
        }


        $details = [];
        foreach($requireInformations as $key=>$infos){
            foreach($infos as $index=>$info){

                if($request->has($info)){
                    if($request->hasFile($info)){
                        if ($file = $request->file($info))
                        {
                           $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                           $file->move('assets/images',$name);
                           $details[$info] = [$name,$key];
                        }
                    }else{
                        $details[$info] = [$request->$info,$key];
                    }
                }
            }
        }
        // $details['type'] = $request->type;
        $user = auth()->user();
        if(!empty($details)){
            $user->kyc_info = json_encode($details,true);
            $user->kyc_status = 3;
        }
        $user->save();
        return redirect()->route('user.dashboard')->with('message','KYC submitted successfully');
    }

    public function kyc_status(Request $request) {
        $user = User::findOrFail($request->id);
        $user->kyc_status = $request->status;
        $gs = Generalsetting::first();
        if($request->status == 2) {
            $user->kyc_token = null;
        }
        if($request->status == 1) {
            $pre_sections = explode(" , ", $user->section);
            $sectionlist = ['Incoming', 'External Payments', 'Request Money', 'Transactions', 'Payments', 'Payment between accounts', 'Exchange Money'];
            foreach($sectionlist as $key=>$section){
                if (!$user->sectionCheck($section)) {
                    $manualfee = Charge::where('user_id', $request->id )->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    if(!$manualfee) {
                        $manualfee = Charge::where('user_id', 0)->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    }
                    if($manualfee && $manualfee->data->fixed_charge > 0) {
                        $trans = new Transaction();
                        $trans->trnx = str_rand();
                        $trans->user_id     = $request->id;
                        $trans->user_type   = 1;
                        $trans->currency_id = defaultCurr();
                        $trans->amount      = 0;
                        $trans_wallet = get_wallet($request->id, defaultCurr(), 1);
                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                        $trans->charge      = $manualfee->data->fixed_charge;
                        $trans->type        = '-';
                        $trans->remark      = 'module';
                        $trans->details     = $section.trans(' Section Create');
                        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                        $trans->save();

                        user_wallet_decrement($request->id, defaultCurr(), $manualfee->data->fixed_charge, 1);
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
        }
        $user->update();
        $res = $request->status == 1 ? 'Your Verification Completed.' : 'Your Verification Rejected.';
        $res_status = $request->status == 1 ? 'message' : 'error';
        return redirect(route('user.dashboard'))->with($res_status, $res);
    }
}
