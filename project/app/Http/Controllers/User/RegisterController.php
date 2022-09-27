<?php

namespace App\Http\Controllers\User;

use Auth;
use Session;
use Validator;
use App\Models\Plan;
use App\Models\User;
use App\Models\Currency;
use App\Models\Admin;
use App\Models\BankPlan;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\ReferralBonus;
use App\Models\Wallet;
use App\Models\RequestDomain;
use App\Models\Generalsetting;
use Illuminate\Support\Carbon;
use App\Models\UserSubscription;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegisterForm(Request $request)
    {
        //$data = BankPlan::findOrFail($id);
        //return view('user.register', compact('data'));
        return view('user.register');
    }

    public function showDomainRegisterForm($id)
    {
        $data = Plan::findOrFail($id);
        return view('user.domainregister', compact('data'));
    }

    public function domainRegister(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:admins,email,',
                'domains' => 'required|unique:domains,domain',
                'password' => 'same:password_confirmation',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $subscription = Plan::findOrFail($id);

        $domain = new RequestDomain();
        $domain->name = $request->name;
        $domain->email = $request->email;
        $domain->password = Hash::make($request->password);
        $domain->domain_name = $request->domains;
        $domain->type = 'Admin';
        $domain->save();

        $insAdmin = new Admin();
        $insAdmin->name = $request->iname;
        $insAdmin->email = $request->iemail;
        $insAdmin->phone = $request->iphone;
        $insAdmin->vat = $request->vat;
        $insAdmin->address = $request->address;
        $insAdmin->city = $request->city;
        $insAdmin->zip = $request->zip;
        $insAdmin->country_id = $request->country_id;
        $insAdmin->password = Hash::make($request->password);

        $insAdmin->plan_id =$subscription->id;
        $insAdmin->status = 0;
        $insAdmin->save();

        $msg = 'Registed Successfully.'.'<a href="'.route("admin.login", $insAdmin->id).'">Go admin site</a>';
        return response()->json($msg);
    }

    public function register(Request $request)
    {
        $value = session('captcha_string');
        if ($request->codes != $value) {
            return response()->json(array('errors' => [0 => 'Please enter Correct Capcha Code.']));
        }

        $rules = [
            'email'   => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required||min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $gs = Generalsetting::first();
        //$subscription = BankPlan::findOrFail($id);
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
        $input['name'] = trim($request->firstname)." ".trim($request->lastname);
        $input['dob'] = $request->customer_dob;

        if($request->form_select == 1) {
            $input['company_name'] = $request->company_name;
            $input['company_reg_no'] = $request->company_reg_no;
            $input['company_vat_no'] = $request->company_vat_no;
            $input['company_address'] = $request->company_address;
            $input['company_dob'] = $request->company_dob;
            $input['personal_code'] = $request->personal_code;
            $input['your_id'] = $request->your_id;
            $input['issued_authority'] = $request->issued_authority;
            $input['date_of_issue'] = $request->date_of_issue;
            $input['date_of_expire'] = $request->date_of_expire;
        }

        $user->fill($input)->save();

        $default_currency = Currency::where('is_default','1')->first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = $user->id;
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $default_currency->id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 1;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

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
                    // $mainUser->balance += $gs->affilate_user;
                    // $mainUser->update();

                    // $user->balance += $gs->affilate_new_user;
                    // $user->update();
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
                    $mainUserTrans->data        = '{"sender":"System Account", "receiver":"'.$mainUser->name.'"}';
                    $mainUserTrans->details     = trans('Referral Bonus');

                    // $mainUserTrans->email = $mainUser->email;
                    // $mainUserTrans->amount = $gs->affilate_user;
                    // $mainUserTrans->type = "Referral Bonus";
                    // $mainUserTrans->user_type = 1;
                    // $mainUserTrans->currency_id = $currency;
                    // $mainUserTrans->profit = 0;//"plus";
                    // $mainUserTrans->txnid = Str::random(12);
                    // $mainUserTrans->user_id = $mainUser->id;
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
                    $newUserTrans->data        = '{"sender":"System Account", "receiver":"'.$user->name.'"}';
                    $newUserTrans->details     = trans('Referral Bonus');

                    // $newUserTrans->email = $user->email;
                    // $newUserTrans->amount = $gs->affilate_user;
                    // $newUserTrans->type = "Referral Bonus";
                    // $newUserTrans->user_type = 1;
                    // $newUserTrans->currency_id = $currency;
                    // $newUserTrans->profit = 0;//"plus";
                    // $newUserTrans->txnid = Str::random(12);
                    // $newUserTrans->user_id = $user->id;
                    $newUserTrans->save();
                }
            }

            $user->email_verified = 'Yes';
            $user->update();
            $notification = new Notification;
            $notification->user_id = $user->id;
            $notification->save();
            Auth::guard('web')->login($user);

            return response()->json(1);
        }
    }

    public function token($token)
    {
        $gs = Generalsetting::findOrFail(1);
        if ($gs->is_verification_email == 1) {
            $user = User::where('verification_link', '=', $token)->first();
            if (isset($user)) {
                $user->email_verified = 'Yes';
                $user->update();

                if (Session::has('affilate')) {
                    $referral = User::findOrFail(Session::get('affilate'));
                    $user->referral_id = $referral->id;
                    $user->update();
                }

                if ($gs->is_affilate == 1 && Session::has('affilate')) {
                    $mainUser = $referral;
                    $mainUser->income += $gs->affilate_user;
                    $mainUser->update();

                    $user->income += $gs->affilate_new_user;
                    $user->update();
                }


                $notification = new Notification;
                $notification->user_id = $user->id;
                $notification->save();
                Auth::guard('web')->login($user);
                return redirect()->route('user.dashboard')->with('success', 'Email Verified Successfully');
            }
        } else {
            return redirect()->back();
        }
    }
}
