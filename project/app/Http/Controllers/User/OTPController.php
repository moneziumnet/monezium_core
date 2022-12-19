<?php

namespace App\Http\Controllers\User;

use App\Classes\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\Pagesetting;
use App\Models\Generalsetting;
use Validator;

class OTPController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showgoogleotpForm()
    {
        return view('user.googleotp');
    }

    public function showotpForm()
    {
        return view('user.otp');
    }

    public function googleotp(Request $request)
    {
        $request->validate([
          'otp' => 'required'
        ]);

        $user = auth()->user();
        $googleAuth = new GoogleAuthenticator();
        $otp =  $request->otp;

        $secret = $user->go;
        $oneCode = $googleAuth->getCode($secret);
        $userOtp = $otp;
        if ($oneCode == $userOtp) {
            $user->verified = 1;
            $user->save();
            return redirect()->route('user.dashboard');
        } else {
          return redirect()->back()->with('error','OTP not match!');
        }
    }

    public function otp(Request $request)
    {
        $request->validate([
          'otp' => 'required'
        ]);

        $user = auth()->user();
        $otp =  $request->otp;

        $userOtp = $otp;
        if ($user->two_fa_code == $userOtp) {
            $user->verified = 1;
            $user->save();
            return redirect()->route('user.dashboard');
        } else {
          return redirect()->back()->with('error','OTP not match!');
        }
    }

    public function sendotp() {
        $user = auth()->user();
        try {
            if($user->payment_fa == 'two_fa_email') {
                $verification_code = rand(100000, 999999);
                $gs = Generalsetting::first();
                $to = $user->email;
                $subject = "Verify your email address";
                $msg_body = "To verify your email address use this security code: ".$verification_code;
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                sendMail($to,$subject,$msg_body,$headers);
                $user->two_fa_code = $verification_code;
                $user->update();
                return 'success';
            }
            elseif ($user->payment_fa == 'two_fa_phone') {
                $verification_code = rand(100000, 999999);
                sendSMS($user->phone,'To verify your email address use this security code: '.$verification_code,Pagesetting::value('phone'));
                $user->two_fa_code = $verification_code;
                $user->update();
                return 'success';
            }
            elseif ($user->payment_fa == 'two_fa_google') {
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                $user->two_fa_code = $oneCode;
                $user->update();
                return 'success';
            }
        } catch (\Throwable $th) {
            return 'fail';
        }
        return 'fail';
    }
}
