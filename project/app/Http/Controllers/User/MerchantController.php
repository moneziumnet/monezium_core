<?php

namespace App\Http\Controllers\User;

use Image;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ApiCreds;
use Auth;

class MerchantController extends Controller
{
    public function generateQR()
    {
        $user = Auth::user();
        return view('user.merchant.qr',compact('user'));
    }

    public function apiKeyForm()
    {
        $cred = ApiCreds::whereMerchantId(merchant()->id)->first();
        if(!$cred){
            $cred = ApiCreds::create([
                'merchant_id' => merchant()->id,
                'access_key'  => (string) Str::uuid(),
                'mode'        => 0
            ]); 
        }
        return view('user.merchant.api_key_form',compact('cred'));
    }

    public function apiKeyGenerate()
    {
        $cred = ApiCreds::whereMerchantId(merchant()->id)->first();
        if(!$cred){
            ApiCreds::create([
                'merchant_id' => merchant()->id,
                'access_key'  => (string) Str::uuid(),
                'mode'        => 0
            ]); 
        }
        $cred->access_key = (string) Str::uuid();
        $cred->update();
        return back()->with('success','New api key has been generated');
    }

    public function downloadQR($email)
    {
        $file = generateQR($email);
        $file = file_get_contents($file);
        $image = Image::make($file);
        $extension = str_replace('image/','',$image->mime);
        $filename = 'QrCode_'.$email.'_.'.$extension;
        $qrCode = $image->opacity(100)->fit(350,350);
        $qrCode->encode('jpg');
    
        $headers = [
            'Content-Type' => $image->mime,
            'Content-Disposition' => 'attachment; filename='.$filename,
        ];
        return response()->stream(function() use ($qrCode) {
            echo $qrCode;
        }, 200, $headers);
    }
}
