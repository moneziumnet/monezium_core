<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BankPlan;
use App\Models\AdminUserConversation;
use App\Models\Generalsetting;
use App\Models\Notification;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (Hash::check($request->password, $user->password)){
            $cred = UserApiCred::whereUserId($user->id)->first();
            $user->access_key = $cred->access_key;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $user]);
        } else {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid email/password']);
        }
    }

    public function register(Request $request)
    {
        try {
            //code...
            $rules = [
                'email'   => 'required|email|unique:users',
                'phone' => 'required',
                'password' => 'required|min:6'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $gs = Generalsetting::first();
            $subscription = BankPlan::findOrFail(1);

            $user = new User;
            $input = $request->all();
            $input['bank_plan_id'] = $subscription->id;
            $input['plan_end_date'] = Carbon::now()->addDays($subscription->days);//Carbon::now()->addDays(365);
            $input['password'] = bcrypt($request['password']);
            $input['account_number'] = $gs->account_no_prefix . date('ydis') . random_int(100000, 999999);
            $token = md5(time() . $request->name . $request->email);
            $input['verification_link'] = $token;
            $input['referral_id'] = $request->input('reff')? $request->input('reff'):'0';
            $input['affilate_code'] = md5($request->name . $request->email);
            $user->fill($input)->save();

            if ($gs->is_verification_email == 1) {
                $verificationLink = "<a href=" . url('user/register/verify/' . $token) . ">Simply click here to verify. </a>";
                $to = $request->email;
                $subject = 'Verify your email address.';
                $msg = "Dear Customer,<br> We noticed that you need to verify your email address." . $verificationLink;


                    $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                    mail($to, $subject, $msg, $headers);
                return response()->json('We need to verify your email address. We have sent an email to ' . $to . ' to verify your email address. Please click link in that email to continue.');
            } else {

                if (Session::has('affilate')) {
                    $referral = User::findOrFail(Session::get('affilate'));
                    $user->referral_id = $referral->id;
                    $user->update();
                }

                if ($gs->is_affilate == 1) {
                    if (Session::has('affilate')) {

                        $mainUser = User::findOrFail(Session::get('affilate'));
                        $currency = Currency::whereIsDefault(1)->first()->id;
                        user_wallet_increment($mainUser->id, $currency, $gs->affilate_user);

                        user_wallet_increment($user->id, $currency, $gs->affilate_new_user);

                        $bonus = new ReferralBonus();
                        $bonus->from_user_id = $user->id;
                        $bonus->to_user_id = $mainUser->id;
                        $bonus->amount = $gs->affilate_user;
                        $bonus->type = 'Register';
                        $bonus->save();

                        $mainUserTrans = new Transaction();
                        $mainUserTrans->trnx        = str_rand();
                        $mainUserTrans->user_id     = $mainUser->id;
                        $mainUserTrans->user_type   = 1;
                        $mainUserTrans->currency_id = $currency;
                        $mainUserTrans->amount      = $gs->affilate_user;
                        $mainUserTrans->charge      = 0;
                        $mainUserTrans->type        = '+';
                        $mainUserTrans->remark      = 'Referral Bonus';
                        $mainUserTrans->details     = trans('Referral Bonus');

                        $mainUserTrans->save();

                        $newUserTrans = new Transaction();
                        $newUserTrans->trnx        = str_rand();
                        $newUserTrans->user_id     = $user->id;
                        $newUserTrans->user_type   = 1;
                        $newUserTrans->currency_id = $currency;
                        $newUserTrans->amount      = $gs->affilate_new_user;
                        $newUserTrans->charge      = 0;
                        $newUserTrans->type        = '+';
                        $newUserTrans->remark      = 'Referral Bonus';
                        $newUserTrans->details     = trans('Referral Bonus');

                        $newUserTrans->save();
                    }
                }

                $user->email_verified = 'Yes';
                $user->update();
                $notification = new Notification;
                $notification->user_id = $user->id;
                $notification->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function forgot(Request $request)
    {
        try {
            $gs = Generalsetting::findOrFail(1);
            $rules = [
                'email'   => 'required|email'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $input =  $request->all();

            if (User::where('email', '=', $request->email)->count() > 0) {

              $admin = User::where('email', '=', $request->email)->firstOrFail();
              $autopass = Str::random(8);
              $input['password'] = bcrypt($autopass);
              $admin->update($input);
              $subject = "Reset Password Request";
              $msg = "Your New Password is : ".$autopass;

                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($request->email,$subject,$msg,$headers);
              return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Your Password Reseted Successfully. Please Check your email for new Password.']);

            }
            else{
              return  response()->json(['status' => '401', 'error_code' => '0', 'message' => 'No Account Found With This Email.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }

    }

    public function dashboard(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['user'] = User::whereId($user_id)->first();
            $wallets = Wallet::where('user_id',$user_id)->where('user_type',1)->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['transactions'] = Transaction::whereUserId($user_id)->orderBy('id','desc')->limit(5)->get();
            foreach ($data['transactions'] as $key => $transaction) {
                $transaction->currency = Currency::whereId($transaction->currency_id)->first();
            }
            $data['userBalance'] = userBalance($user_id);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function packages(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['packages'] = BankPlan::orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function changepassword(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $rules = [
                'current_password'   => 'required',
                'new_password'   => 'required',
                'confirm_new_password'   => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $input =  $request->all();

            $user = User::whereId($user_id)->first();
                if ($request->current_password){
                    if (Hash::check($request->current_password, $user->password)){
                        if ($request->new_password == $request->confirm_new_password){
                            $input['password'] = Hash::make($request->new_password);
                        }else{
                            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Confirm password does not match.']);
                        }
                    }else{
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Current password Does not match.']);
                    }
                }
                if($user->update($input))
                {
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Password has been changed']);
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
                }

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }

    }

    public function supportmessage(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['tickets'] = AdminUserConversation::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

}
