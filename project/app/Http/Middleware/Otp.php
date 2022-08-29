<?php

namespace App\Http\Middleware;

use App\Models\Generalsetting;
use App\Models\Pagesetting;
use Illuminate\Support\Facades\Auth;
use Closure;

class Otp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $gs = Generalsetting::first();

        $user = auth()->user();
        if(!$user) {
            return $next($request);
        }
        if($user->login_fa_yn =="Y"){
            if($user->login_fa == "two_fa_email" || $user->login_fa == "two_fa_phone"){
                if($user->verified == 0){
                    if($user->login_fa == "two_fa_email"){
                        $verification_code = rand(100000, 999999);
                        $gs = Generalsetting::first();
                        $to = $user->email;
                        $subject = "Verify your email address";
                        $msg_body = "To verify your email address use this security code: ".$verification_code;
                        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                        $headers .= "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        mail($to,$subject,$msg_body,$headers);
                        $user->two_fa_code = $verification_code;
                        $user->save();

                    }
                    if($user->login_fa == "two_fa_phone"){
                        $verification_code = rand(100000, 999999);
                        sendSMS($user->phone,'To verify your email address use this security code: '.$verification_code,Pagesetting::value('phone'));
                        $user->two_fa_code = $verification_code;
                        $user->save();
                    }
                    return redirect()->route('user.otp');
                }
                return $next($request);
            }else{
                if($user->verified == 0){
                    return redirect()->route('user.googleotp');
                }
                return $next($request);
            }
        }else{
            $user->verified = 1;
            $user->save();
        }
        return $next($request);
    }
}
