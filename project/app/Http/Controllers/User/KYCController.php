<?php

namespace App\Http\Controllers\User;

use App\Models\KycForm;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
                $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();
                return view('user.kyc.index',compact('userType','userForms'));
            }else{
                return redirect()->route('user.dashboard')->with('unsuccess','You have submitted kyc for verification.');
            }
        }

    }

    public function onlineSelfie($id){
        $user_id = decrypt($id);
        return view('user.kyc.selfie',compact('user_id'));
    }

    public function takeOnlineSelfie(Request $request){
        /** Direct Photo upload**/
        $img = $request->image;

        $folderPath = "uploads/";

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);
        $fileName = uniqid() . '.png';

        $file = $folderPath . $fileName;

        Storage::put($file, $image_base64);

        $user = auth()->user();
        if(!empty($details)){
            $user->kyc_photo = $fileName;
        }
        $user->save();

        return redirect()->route('user.dashboard')->with('message','KYC submitted successfully');
    }

    public function sendSelfieLink(){
        $user = auth()->user();
        $gs = Generalsetting::first();
        $to = $user->email;
        $subject = " Online Selfie Link";
        $msg = "Hello ".$user->name."!\nThis is the link of online Selfie for you.\nLink is \n".url('/user/kyc-take-selfie')." \n Thank you.";
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        mail($to,$subject,$msg,$headers);
    }

    public function kyc(Request $request){
        $userType = 'user';
        $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();

        $user = auth()->user();
        $gs = Generalsetting::first();
        $route = route('user.kyc.selfie',encrypt($user->id));
        if($request->sendlink) {
            $to = $user->email;
            $subject = " Online Selfie Link";
            $msg = "Hello ".$user->name."!\nThis is the link of online Selfie for you.\nLink is \n".$route." \n Thank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
        }

        $requireInformations = [];
        if($userForms){
            foreach($userForms as $key=>$value){
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

        $user = auth()->user();
        if(!empty($details)){
            $user->kyc_info = json_encode($details,true);
            $user->kyc_status = 3;
        }
        $user->save();

        return redirect()->route('user.dashboard')->with('message','KYC submitted successfully');
    }
}
