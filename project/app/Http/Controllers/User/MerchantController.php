<?php

namespace App\Http\Controllers\User;

use Image;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\MediaHelper;
use App\Models\UserApiCred;
use App\Models\MerchantWallet;
use Auth;

class MerchantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $user = Auth::user();

        $cred = UserApiCred::where('user_id',$user->id)->first();
        if(!$cred){
            $userapicred = new UserApiCred();
            $userapicred->user_id = $user->id;
            $userapicred->access_key = (string) Str::uuid();
            $userapicred->mode = 0;

            $userapicred->save();
            $cred = UserApiCred::where('user_id',$user->id)->first();
        }

        $wallets = MerchantWallet::where('merchant_id', auth()->id())->with('currency')->with('shop')->get();

        return view('user.merchant.index',compact('cred', 'user', 'wallets'));
    }

    public function apiKeyGenerate()
    {
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $user = Auth::user();
        $cred = UserApiCred::whereUserId($user->id)->first();
        if(!$cred){
            UserApiCred::create([
                'merchant_id' => $user->id,
                'access_key'  => (string) Str::uuid(),
                'mode'        => 0
            ]);
        }
        $cred->access_key = (string) Str::uuid();
        $cred->update();
        return back()->with('success','New api key has been generated');
    }

    public function downloadQR(Request $request)
    {
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $file = generateQR($request->email);
        $file = file_get_contents($file);
        $image = Image::make($file);
        $extension = str_replace('image/','',$image->mime);
        $filename = 'QrCode_'.$request->email.'_.'.$extension;
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
