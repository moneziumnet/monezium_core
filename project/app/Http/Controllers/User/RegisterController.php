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
use App\Models\Charge;
use Illuminate\Http\Request;
use Request as facade_request;

use App\Models\ReferralBonus;
use App\Models\Wallet;
use App\Models\RequestDomain;
use App\Models\Generalsetting;
use App\Models\LoginActivity;
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

    public function showRegisterForm(Request $request, $id)
    {
        //$data = BankPlan::findOrFail($id);
        //return view('user.register', compact('data'));
        return view('user.register', compact('id'));
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

    public function register(Request $request, $id)
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
        $subscription = BankPlan::findOrFail($id);

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
        $input['phone'] = preg_replace("/[^0-9]/", "", $request->phone);

        if($request->form_select == 1) {
            $subscription = BankPlan::where('type', 'corporate')->where('keyword', $subscription->keyword)->first();
            $input['bank_plan_id'] = $subscription->id;
            $input['plan_end_date'] = Carbon::now()->addDays($subscription->days);
            $input['company_name'] = $request->company_name;
            $input['company_reg_no'] = $request->company_reg_no;
            $input['company_vat_no'] = $request->company_vat_no;
            $input['company_dob'] = $request->company_dob;
            $input['company_type'] = $request->company_type;
            $input['company_city'] = $request->company_city;
            $input['company_country'] = $request->company_country;
            $input['company_zipcode'] = $request->company_zipcode;
            $input['company_address'] = $request->company_address;
            $input['personal_code'] = null;
            $input['your_id'] = null;
            $input['issued_authority'] = null;
            $input['date_of_issue'] = null;
            $input['date_of_expire'] = null;
        }

        if($request->form_select == 0) {
            $input['personal_code'] = $request->personal_code;
            $input['your_id'] = $request->your_id;
            $input['issued_authority'] = $request->issued_authority;
            $input['date_of_issue'] = $request->date_of_issue;
            $input['date_of_expire'] = $request->date_of_expire;
            $input['company_type'] = null;
            $input['company_city'] = null;
            $input['company_country'] = null;
            $input['company_zipcode'] = null;
            $input['company_name'] = null;
            $input['company_reg_no'] = null;
            $input['company_vat_no'] = null;
            $input['company_dob'] = null;
            $input['company_address'] = null;
        }

        $user->fill($input)->save();

        $default_currency = Currency::where('is_default','1')->first();
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }

        $user_wallet = new Wallet();
        $user_wallet->user_id = $user->id;
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $default_currency->id;
        $user_wallet->balance = -1 * ($chargefee->data->fixed_charge + $subscription->amount);
        $user_wallet->wallet_type = 1;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $default_currency->id;
        $trans->amount      = 0;
        $trans_wallet       = get_wallet($user->id, $default_currency->id, 1);
        $trans->wallet_id   = $user_wallet->id;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $default_currency->id;
        $trans->amount      = $subscription->amount;
        $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
        $trans->wallet_id   = $user_wallet->id;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'price_plan';
        $trans->details     = trans('Price Plan');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();

        if ($gs->is_verification_email == 1) {
            $verificationLink = "<a href=" . url('user/register/verify/' . $token) . ">Simply click here to verify. </a>";
            $to = $request->email;
            $subject = 'Verify your email address.';
            $msg = "Dear Customer,<br> We noticed that you need to verify your email address." . $verificationLink;

                $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                sendMail($to, $subject, $msg, $headers);
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

                    $trans_wallet = get_wallet($mainUser->id, $currency);
                    $mainUserTrans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $mainUserTrans->currency_id = $currency;
                    $mainUserTrans->amount      = $gs->affilate_user;
                    $mainUserTrans->charge      = 0;
                    $mainUserTrans->type        = '+';
                    $mainUserTrans->remark      = 'Referral Bonus';
                    $mainUserTrans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($mainUser->company_name ?? $mainUser->name).'"}';
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

                    $trans_wallet = get_wallet($user->id, $currency);
                    $newUserTrans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $newUserTrans->amount      = $gs->affilate_new_user;
                    $newUserTrans->charge      = 0;
                    $newUserTrans->type        = '+';
                    $newUserTrans->remark      = 'Referral Bonus';
                    $newUserTrans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
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

            $activity = new LoginActivity();
            $activity->subject = 'User Register Successfully.';
            $activity->url = facade_request::fullUrl();
            $activity->ip = $request->global_ip;
            $activity->agent = facade_request::header('user-agent');
            $activity->user_id = $user->id;
            $activity->save();

            $notification = new Notification;
            $notification->user_id = $user->id;
            $notification->save();
            Auth::guard('web')->login($user);
            if(session()->get('setredirectroute') != NULL){
                $redirect_url = session()->get('setredirectroute');
                session()->forget('setredirectroute');
                return response()->json(["redirect_url" => $redirect_url]);
            }
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
