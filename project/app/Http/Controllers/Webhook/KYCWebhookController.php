<?php

namespace App\Http\Controllers\Webhook;

use App\Models\User;
use App\Models\KycForm;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Classes\SumsubKYC;

class KYCWebhookController extends Controller
{
    public function kyc_status(Request $request){
        $userType = 'user';
        $userForms = KycForm::where('user_type',$userType == 'user' ? 1 : 2)->get();

        $user = auth()->user();
        $gs = Generalsetting::first();
        $route = route('user.kyc.selfie',encrypt($user->id));
        $to = $user->email;
        $subject = " Online Selfie Link";
        $msg = "Hello ".$user->name."!\nThis is the link of online Selfie for you.\nLink is \n".$route." \n Thank you.";
        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        mail($to,$subject,$msg,$headers);

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
        $details['type'] = $request->type;
        $user = auth()->user();
        if(!empty($details)){
            $user->kyc_info = json_encode($details,true);
            $user->kyc_status = 3;
        }
        $user->save();
        return redirect()->route('user.dashboard')->with('message','KYC submitted successfully');
    }
}
