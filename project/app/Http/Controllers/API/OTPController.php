<?php

namespace App\Http\Controllers\API;

use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\Pagesetting;
use App\Models\Generalsetting;
use Validator;

class OTPController extends Controller
{

    public function googleotp(Request $request)
    {
        try {
            $rules = [
              'otp' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $user = auth()->user();
            $googleAuth = new GoogleAuthenticator();
            $otp =  $request->otp;

            $secret = $user->go;
            $oneCode = $googleAuth->getCode($secret);
            $userOtp = $otp;
            if ($oneCode == $userOtp) {
                $user->verified = 1;
                $user->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'OTP Code is matched correctly.']);
            } else {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'OTP not match!']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function otp(Request $request)
    {
        try {
            $rules = [
                'otp' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $user = auth()->user();
            $otp =  $request->otp;

            $userOtp = $otp;
            if ($user->two_fa_code == $userOtp) {
                $user->verified = 1;
                $user->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'OTP Code is matched correctly.']);
            } else {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'OTP not match!']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function sendotp() {
        try {
            $user = auth()->user();
            if($user->payment_fa == 'two_fa_email') {
                $verification_code = rand(100000, 999999);
                mailSend('verify_code',['code'=>$verification_code], $user);

                $user->two_fa_code = $verification_code;
                $user->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'OTP code is sent correctly.']);
            }
            elseif ($user->payment_fa == 'two_fa_phone') {
                $verification_code = rand(100000, 999999);
                sendSMS($user->phone,'To verify your email address use this security code: '.$verification_code,Pagesetting::value('phone'));
                $user->two_fa_code = $verification_code;
                $user->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'OTP code is sent correctly.']);
            }
            elseif ($user->payment_fa == 'two_fa_google') {
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                $user->two_fa_code = $oneCode;
                $user->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'OTP code is sent correctly.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
