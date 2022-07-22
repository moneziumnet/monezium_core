<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BankPlan;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Models\SaveAccount;
use App\Models\Notification;
use App\Models\Currency;
use App\Models\UserLoan;
use App\Models\UserDps;
use App\Models\DpsPlan;
use App\Models\LoanPlan;
use App\Models\FdrPlan;
use App\Models\UserFdr;
use App\Models\Escrow;
use App\Models\Invoice;
use App\Models\InvItem;
use App\Models\MoneyRequest;
use App\Models\InstallmentLog;
use App\Models\Voucher;
use App\Models\Charge;
use App\Models\ExchangeMoney;
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
                'password' => 'required||min:6|confirmed'
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

                if ($gs->is_smtp == 1) {

                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host       = $gs->smtp_host;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $gs->smtp_user;
                        $mail->Password   = $gs->smtp_pass;
                        if ($gs->smtp_encryption == 'ssl') {
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        } else {
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        }
                        $mail->Port       = $gs->smtp_port;
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom($gs->from_email, $gs->from_name);
                        $mail->addAddress($user->email, $user->name);
                        $mail->addReplyTo($gs->from_email, $gs->from_name);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body    = $msg;
                        $mail->send();
                    } catch (Exception $e) {
                    }
                } else {
                    $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                    mail($to, $subject, $msg, $headers);
                }
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
            $input =  $request->all();

            if (User::where('email', '=', $request->email)->count() > 0) {

              $admin = User::where('email', '=', $request->email)->firstOrFail();
              $autopass = Str::random(8);
              $input['password'] = bcrypt($autopass);
              $admin->update($input);
              $subject = "Reset Password Request";
              $msg = "Your New Password is : ".$autopass;

              if($gs->is_smtp == 1)
              {
                  $data = [
                    'to' => $request->email,
                    'subject' => $subject,
                    'body' => $msg,
                  ];

                  $mailer = new GeniusMailer();
                  $mailer->sendCustomMail($data);
              }
              else
              {
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($request->email,$subject,$msg,$headers);
              }
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
//////////////////////////////////////////////// Loan api ////////////////////////////////////////////////////
    public function loan_index(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['loans'] = UserLoan::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanplan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if ($user_id)
            {
                $data['plans'] = LoanPlan::orderBy('id','desc')->whereStatus(1)->paginate(12);
                $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function pendingloan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['loans'] = UserLoan::whereStatus(0)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningloan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['loans'] = UserLoan::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function paidloan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['loans'] = UserLoan::whereStatus(3)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function rejectedloan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['loans'] = UserLoan::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanamount(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if ($user_id) {
                $plan = LoanPlan::whereId($request->planId)->first();
                $amount = $request->amount;

                if($amount >= $plan->min_amount && $amount <= $plan->max_amount){
                    $data['data'] = $plan;
                    $data['loanAmount'] = $amount;
                    $data['currencyinfo'] = Currency::whereId($request->currency_id)->first();
                    $data['perInstallment'] = ($amount * $plan->per_installment)/100;
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Money should be between minium and maximum amount!']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }

    }

    public function loanrequest(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $user = User::whereId($user_id)->first();
            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to loan.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $monthlyLoans = UserLoan::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus('approve')->sum('loan_amount');

            if($monthlyLoans > $bank_plan->loan_amount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly loan limit over.']);
            }
            $data = new UserLoan();
            $input = $request->all();

            $loan = LoanPlan::findOrFail($request->plan_id);

            $requireInformations = [];
            if($loan->required_information){
                foreach(json_decode($loan->required_information) as $key=>$value){
                    $requireInformations[$value->type][$key] = str_replace(' ', '_', $value->field_name);
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

            if(!empty($details)){
                $input['required_information'] = json_encode($details,true);
            }

            $txnid = Str::random(4).time();
            $input['transaction_no'] = $txnid;
            $input['user_id'] = $user_id;
            $input['next_installment'] = now()->addDays($loan->installment_interval);
            $input['given_installment'] = 0;
            $input['paid_amount'] = 0;
            $input['total_amount'] = $request->loan_amount;
            $input['currency_id'] = $request->currency_id;
            $data->fill($input)->save();

            $trans = new Transaction();
            $trans->trnx = $txnid;
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $request->currency_id;
            $trans->amount      = $request->loan_amount;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'loan_create';
            $trans->details     = trans('loan requesting');
            $trans->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Loan Requesting Successfully']);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanfinish(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if ($user_id)
            {
                $loan = UserLoan::whereId($request->planId)->where('user_id', $user_id)->first();
                if($loan){
                    $plan = LoanPlan::whereId($loan->planId)->first();
                    user_wallet_decrement($loan->user_id, $loan->currency_id, $loan->loan_amount, 4);

                    $loan->status = 3;
                    $loan->next_installment = NULL;
                    $loan->update();
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Finish Requesting Successfully']);
                }else {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'There is not your loan plan.']);

                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function loanlog(Request $request, $id)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if ($user_id) {
                $loan = UserLoan::findOrfail($id);
                $logs = InstallmentLog::whereTransactionNo($loan->transaction_no)->whereUserId($user_id)->orderby('id','desc')->paginate(20);
                $currency = Currency::whereId($loan->currency->id)->first();
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('logs','currency')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

////////////////////////////////////Send Money//////////////////////////////////////////
    public function sendmoney(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $request->validate([
                'account_number'    => 'required',
                'wallet_id'         => 'required',
                'account_name'      => 'required',
                'amount'            => 'required|numeric|min:0',
                'description'       => 'required',
                'code'              => 'required'
            ]);

            $user = User::whereId($user_id)->first();
            $ga = new GoogleAuthenticator();
            $secret = $user->go;
            $oneCode = $ga->getCode($secret);

            if ($oneCode != $request->code) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Two factor authentication code is wrong.']);
            }

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();

            $currency_id = $wallet->currency->id; //Currency::whereId($wallet_id)->first()->id;

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailySend = BalanceTransfer::whereUserId($user_id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

            if($dailySend > $bank_plan->daily_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily send limit over.']);
            }

            if($monthlySend > $bank_plan->monthly_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly send limit over.']);
            }

            $gs = Generalsetting::first();

            if($request->account_number == $user->account_number){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!!']);
            }

            if($request->amount < 0){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be greater than this!']);
            }

            if($request->amount > user_wallet_balance($user_id, $currency_id, $wallet->wallet_type)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance.']);
            }

            if($receiver = User::where('account_number',$request->account_number)->first()){
                $txnid = Str::random(4).time();

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'Send_Money';
                $trans->details     = trans('Send Money');
                $trans->save();


                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $receiver->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'Recieve_Money';
                $trans->details     = trans('Send Money');
                $trans->save();

                session(['sendstatus'=>1, 'saveData'=>$trans]);
                // user_wallet_decrement($user->id, $currency_id, $request->amount);
                // user_wallet_increment($receiver->id, $currency_id, $request->amount);

                user_wallet_decrement($user->id, $currency_id, $request->amount, $wallet->wallet_type);
                user_wallet_increment($receiver->id, $currency_id, $request->amount, $wallet->wallet_type);
                if(SaveAccount::whereUserId($user_id)->where('receiver_id',$receiver->id)->exists()){
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Send Successfully.']);
                }

                if($gs->is_smtp == 1)
                {
                    $data = [
                        'to' => $receiver->email,
                        'type' => "send money",
                        'cname' => $receiver->name,
                        'oamount' => $request->amount,
                        'aname' => "",
                        'aemail' => "",
                        'wtitle' => "",
                    ];

                    $mailer = new GeniusMailer();
                    $mailer->sendAutoMail($data);
                }
                else
                {
                    $to = $receiver->email;
                    $subject = " Money send successfully.";
                    $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                    mail($to,$subject,$msg,$headers);
                }
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Send Successfully.']);
            }else{
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Sender not found!.']);

            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function requestmoney(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $request->validate([
                'account_name' => 'required',
                'wallet_id' => 'required',
                'amount' => 'required|gt:0',
            ]);

            $user = User::whereId($user_id)->first();

            if($user->bank_plan_id === null){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailyRequests = MoneyRequest::whereUserId($user_id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
            $monthlyRequests = MoneyRequest::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');

            $gs = Generalsetting::first();

            if($request->account_number == $user->account_number){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!']);

            }

            $receiver = User::where('account_number',$request->account_number)->first();
            if($receiver === null){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'No register user with this email!']);
            }

            if($dailyRequests > $bank_plan->daily_receive){
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily request limit over.']);
            }

            if($monthlyRequests > $bank_plan->monthly_receive){
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly request limit over.']);
            }

            $cost = $gs->fixed_request_charge + ($request->amount/100) * $gs->percentage_request_charge;
            $finalAmount = $request->amount + $cost;

            $txnid = Str::random(4).time();

            $data = new MoneyRequest();
            $data->user_id =$user_id;
            $data->receiver_id = $receiver->id;
            $data->receiver_name = $receiver->name;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->wallet_id;
            $data->cost = $cost;
            $data->amount = $request->amount;
            $data->status = 0;
            $data->details = $request->details;
            $data->user_type = 1;
            $data->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Request Money Send Successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function approvemoney(Request $request, $id)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $user = User::whereId($user_id)->first();
            if($uesr->twofa != 1)
            {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You must be enable 2FA Security.']);
            }

            $request->validate([
                'code' => 'required'
            ]);

            $ga = new GoogleAuthenticator();
            $secret = $user->go;
            $oneCode = $ga->getCode($secret);

            if ($oneCode != $request->code) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Two factor authentication code is wrong.']);
            }

            $data = MoneyRequest::findOrFail($id);
            $gs = Generalsetting::first();

            $currency_id = Currency::whereIsDefault(1)->first()->id;
            $sender = User::whereId($data->receiver_id)->first();
            $receiver = User::whereId($data->user_id)->first();

            if($data->amount > user_wallet_balance($sender->id, $currency_id)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You don,t have sufficient balance!']);
            }

            $finalAmount = $data->amount - $data->cost;

            user_wallet_decrement($sender->id, $currency_id, $data->amount);
            user_wallet_increment($receiver->id, $currency_id, $finalAmount);

            $data->update(['status'=>1]);

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = $user_id;
            $trans->user_type   = $data->user_type;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans->amount      = $data->amount;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Request_Money';
            $trans->details     = trans('Request Money');

            $trans->save();

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = $receiver->id;
            $trans->user_type   = $data->user_type;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans->amount      = $data->amount;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Request_Money';
            $trans->details     = trans('Request Money');

            $trans->save();

            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $receiver->email,
                    'type' => "request money",
                    'cname' => $receiver->name,
                    'oamount' => $finalAmount,
                    'aname' => "",
                    'aemail' => "",
                    'wtitle' => "",
                ];

                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($data);
            }
            else
            {
                $to = $receiver->email;
                $subject = " Money send successfully.";
                $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($to,$subject,$msg,$headers);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Send.']);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function requestcancel(Request $request, $id)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if ($user_id) {
            $data = MoneyRequest::findOrFail($id);
            $data->update(['status'=>2]);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Request Cancelled.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function receive(Request $request){
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $user = User::whereId($user_id)->first();
            if($user->twofa)
            {
                $data['requests'] = MoneyRequest::orderby('id','desc')->whereReceiverId($user_id)->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Success.', 'data' => $data]);
            }else{

            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You must be enable 2FA Security.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function create(Request $request){
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            if($user_id)
            {
                $wallets = Wallet::where('user_id',$user_id)->with('currency')->get();
                $data['wallets'] = $wallets;
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Success.', 'data' => $data]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

/***DPS API**/
    public function dps_index(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['dps'] = UserDps::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningdps(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['dps'] = UserDps::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function matureddps(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['dps'] = UserDps::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function dpsplan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['plans']          = DpsPlan::whereStatus(1)->orderby('id','desc')->paginate(10);
            $data['currencylist']   = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/**END DPS API**/

/***FDR API**/
    public function fdr_index(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['fdr'] = UserFdr::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningfdr(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['fdr'] = UserFdr::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function closedfdr(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['fdr'] = UserFdr::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function fdrplan(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['plans']          = FdrPlan::whereStatus(1)->orderby('id','desc')->paginate(10);
            $data['currencylist']   = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/**END FDR API**/

/*********************START ESCROW API******************************/
    public function myescrow(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['escrow']          = Escrow::with('currency','recipient')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function makeescrow(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $request->validate([
                'receiver'          => 'required|email',
                'wallet_id'         => 'required|integer',
                'amount'            => 'required|numeric|gt:0',
                'description'       => 'required',
                'charge_pay'        => 'numeric'
            ],
            [
                'wallet_id.required' => 'Wallet is required'
            ]);

            $user = User::whereId($user_id)->first();

            $receiver = User::where('email',$request->receiver)->first();
            if(!$receiver) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Recipient not found']);

            $senderWallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->whereUserId($user_id)->first();

            if(!$senderWallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your wallet not found']);

            $currency = Currency::findOrFail($senderWallet->currency->id);
            $charge = charge('make-escrow');

            $finalCharge = amount(chargeCalc($charge,$request->amount,$currency->rate),$currency->type);

            if($request->pay_charge) $finalAmount =  amount($request->amount + $finalCharge, $currency->type);
            else  $finalAmount =  amount($request->amount, $currency->type);

            if($senderWallet->balance < $finalAmount) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance']);

            $senderWallet->balance -= $finalAmount;
            $senderWallet->update();

            $escrow               = new Escrow();
            $escrow->trnx         = str_rand();
            $escrow->user_id      = $user_id;
            $escrow->recipient_id = $receiver->id;
            $escrow->description  = $request->description;
            $escrow->amount       = $request->amount;
            $escrow->pay_charge   = $request->pay_charge ? 1 : 0;
            $escrow->charge       = $finalCharge;
            $escrow->currency_id  = $currency->id;
            $escrow->save();

            $trnx              = new Transaction();
            $trnx->trnx        = $escrow->trnx;
            $trnx->user_id     = $user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $currency->id;
            $trnx->wallet_id   = $senderWallet->id;
            $trnx->amount      = $finalAmount;
            $trnx->charge      = $finalCharge;
            $trnx->remark      = 'make_escrow';
            $trnx->type        = '-';
            $trnx->details     = trans('Made escrow to '). $receiver->email;
            $trnx->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Escrow has been created successfully', 'data' => $escrow]);

        } catch (\Throwable $th) {
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function escrowpending(Request $request)
    {
        try {
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['escrow']          = Escrow::with('currency')->where('recipient_id',$user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/*********************END ESCROW API******************************/


/*********************START VOUCHER API****************************/

    public function vouchers(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['vouchers']          = Voucher::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function createvoucher(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $request->validate([
                'wallet_id' => 'required|integer',
                'amount' => 'required|numeric|gt:0'
            ]);


        $wallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',$user_id)->first();
        if(!$wallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Wallet not found']);

        $charge = charge('create-voucher');
        $rate = $wallet->currency->rate;

        if($request->amount <  $charge->minimum * $rate || $request->amount >  $charge->maximum * $rate){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please follow the limit']);
        }

        $finalCharge = chargeCalc($charge,$request->amount,$rate);
        $finalAmount = numFormat($request->amount + $finalCharge);

        $commission  = ($request->amount * $charge->commission)/100;

        $voucher = new Voucher();
        $voucher->user_id = $user_id;
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $request->amount;
        $voucher->code = randNum(10).'-'.randNum(10);
        $voucher->save();
        $userBalance = user_wallet_balance($user_id, $wallet->currency_id);
        if($finalAmount > $userBalance) return response()->json(['status' => '401', 'error_code' => $finalAmount, 'message' => 'Wallet has insufficient balance']);

        user_wallet_decrement($user_id,$wallet->currency_id,$finalAmount);
        // $wallet->balance -=  $finalAmount;
        // $wallet->save();

        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = $user_id;
        $trnx->user_type   = 1;
        $trnx->currency_id = $wallet->currency->id;
        $trnx->amount      = $finalAmount;
        $trnx->charge      = $finalCharge;
        $trnx->remark      = 'create_voucher';
        $trnx->type        = '-';
        $trnx->details     = trans('Voucher created');
        $trnx->save();

        user_wallet_increment($user_id,$wallet->currency_id,$commission);
        // $wallet->balance +=  $commission;
        // $wallet->save();

        $commissionTrnx              = new Transaction();
        $commissionTrnx->trnx        = $trnx->trnx;
        $commissionTrnx->user_id     = $user_id;
        $commissionTrnx->user_type   = 1;
        $commissionTrnx->currency_id = $wallet->currency->id;
        $commissionTrnx->amount      = $commission;
        $commissionTrnx->charge      = 0;
        $commissionTrnx->remark      = 'voucher_commission';
        $commissionTrnx->type        = '+';
        $commissionTrnx->details     = trans('Voucher commission');
        $commissionTrnx->save();
        $data['vouchers']          = Voucher::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher has been created successfully', 'data' => $data]);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function reedemvoucher(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $request->validate([
                'code'          => 'required',

            ],
            [
                'code.required' => 'Voucher is required'
            ]);

            $user = User::whereId($user_id)->first();

            $voucher = Voucher::where('code',$request->code)->where('status',0)->first();

            if(!$voucher){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid voucher code']);
            }

            if( $voucher->user_id == $user_id){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Can\'t reedem your own voucher']);
            }

            $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',$user_id)->first();
            if(!$wallet){
                $gs = Generalsetting::first();
                $wallet = Wallet::create([
                    'user_id' => $user_id,
                    'user_type' => 1,
                    'currency_id' => $voucher->currency_id,
                    'balance'   => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);
            }

            $wallet->balance += $voucher->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $wallet->currency->id;
            $trnx->amount      = $voucher->amount;
            $trnx->charge      = 0;
            $trnx->type        = '+';
            $trnx->remark      = 'reedem_voucher';
            $trnx->details     = trans('Voucher reedemed');
            $trnx->save();

            $voucher->status = 1;
            $voucher->reedemed_by = $user_id;
            $voucher->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher reedemed successfully']);


        } catch (\Throwable $th) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
           }
    }

    public function reedemedhistory(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['vouchers'] = Voucher::with('currency')->where('status',1)->where('reedemed_by',$user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

/*********************END VOUCHER API******************************/
    /********** Start Invoice API******/
    public function invoices(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th)
        {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function invoiceview(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $request->validate([
                'number' => 'required'
            ]);

            $number = $request->number;
            //$invoice = Invoice::where('number',decrypt($number))->firstOrFail();
            $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->where('number',$number)->firstOrFail();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }


    public function createinvoice(Request $request)
    {
        try{
            $request->validate([
                'invoice_to' => 'required',
                'email'      => 'required|email',
                'address'    => 'required',
                'currency'   => 'required',
                'item'       => 'required',
                'item.*'     => 'required',
                'amount'     => 'required',
                'amount.*'   => 'required|numeric|gt:0'
            ]);
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $charge = charge('create-invoice');
            $currency = Currency::findOrFail($request->currency);

            $amount = array_sum($request->amount);
            $finalCharge = chargeCalc($charge,$amount,$currency->rate);
            $willGetAmount = numFormat($amount - $finalCharge);

            $invoice = new Invoice();
            $invoice->user_id      = $user_id;
            $invoice->number       = 'INV-'.randNum(8);
            $invoice->invoice_to   = $request->invoice_to;
            $invoice->email        = $request->email;
            $invoice->address      = $request->address;
            $invoice->currency_id  = $currency->id;
            $invoice->charge       = $finalCharge;
            $invoice->final_amount = $amount;
            $invoice->get_amount   = $willGetAmount;
            $invoice->save();

            $items = array_combine($request->item,$request->amount);
            foreach($items as $item => $amount){
                $invItem             = new InvItem();
                $invItem->invoice_id = $invoice->id;
                $invItem->name       = $item;
                $invItem->amount	 = $amount;
                $invItem->save();
            }
            $route = route('invoice.view',encrypt($invoice->number));
            @email([

                'email'   => $invoice->email,
                "subject" => trans('Invoice Payment'),
                'message' => trans('Hello')." $invoice->invoice_to,<br/></br>".

                    trans('You have pending payment of invoice')." <b>$invoice->number</b>.".trans('Please click the below link to complete your payment') .".<br/></br>".

                    trans('Invoice details').": <br/></br>".

                    trans('Amount')  .":  $amount $currency->code <br/>".
                    trans('Payment Link')." :  <a href='$route' target='_blank'>".trans('Click To Payment')."</a><br/>".
                    trans('Time')." : $invoice->created_at,

                "
            ]);
            $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->where('number',$invoice->number)->firstOrFail();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been created', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function invoiceurl(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $request->validate([
                'number' => 'required'
            ]);
            $number = $request->number;

            $route = route('invoice.view',encrypt($number));
            $data['invoice_link'] = $route;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    /********** End Invoice API******/



    /************Start Exchange API***************/
    public function exchangemoneyhistory(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $search = $request->transaction_no;
            $exchanges = ExchangeMoney::whereUserId($user_id)
                        ->when($search,function($q) use($search){
                                return $q->where('trnx',$search);
                            }
                        )->with(['fromCurr','toCurr'])->latest()->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $exchanges]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function exchangerecents(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $recentExchanges = ExchangeMoney::whereUserId($user_id)->with(['fromCurr','toCurr'])->orderBy('id','desc')->take(10)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $recentExchanges]);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function exchangemoney(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $request->validate([
                'amount'            => 'required|gt:0',
                'from_wallet_id'    => 'required|integer',
                'to_wallet_id'      => 'required|integer'
            ],[
                'from_wallet_id.required' => 'From currency is required',
                'to_wallet_id.required' => 'To currency is required',
            ]);

            $charge  = charge('money-exchange');

            $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',$user_id)->where('user_type',1)->firstOrFail();

            $toWallet = Wallet::where('currency_id',$request->to_wallet_id)->where('user_id',$user_id)->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();

        if(!$toWallet){
            $gs = Generalsetting::first();
            $toWallet = Wallet::create([
                'user_id'     => $user_id,
                'user_type'   => 1,
                'currency_id' => $request->to_wallet_id,
                'balance'     => 0,
                'wallet_type' => $request->wallet_type,
                'wallet_no'     => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
            ]);
        }


        $user= User::whereId($user_id)->first();
        $global_charge      = Charge::where('name', 'Exchange Money')->where('plan_id', $user->bank_plan_id)->first();
        $global_cost        = 0;
        $transaction_global_cost = 0;
        $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;



        if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum]);
        }

        $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $custom_cost = 0;
        $transaction_custom_cost = 0;

        $explode = explode(',',$user->user_type);

        if(in_array(3,$explode))
        {
            $custom_charge = Charge::where('name', 'Exchange Money')->where('user_id', $user_id)->first();

            if($custom_charge)
            {
                $custom_cost = $custom_charge->data->fixed_charge + ($request->amount/100) * $custom_charge->data->percent_charge;
            }
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user);
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $defaultAmount = $request->amount / $fromWallet->currency->rate;
        $finalAmount   = amount($defaultAmount * $toWallet->currency->rate,$toWallet->currency->type);

        $charge = amount($custom_cost+$global_cost+$transaction_global_cost+$transaction_custom_cost,$fromWallet->currency->type);
        $totalAmount = amount(($request->amount +  $charge),$fromWallet->currency->type);

        if($fromWallet->balance < $totalAmount){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your '.$fromWallet->currency->code.' wallet']);
        }

        $fromWallet->balance -=  $totalAmount;
        $fromWallet->update();

        $toWallet->balance += $finalAmount;
        $toWallet->update();

        $exchange                   = new ExchangeMoney();
        $exchange->trnx             = str_rand();
        $exchange->from_currency    = $fromWallet->currency->id;
        $exchange->to_currency      = $toWallet->currency->id;
        $exchange->user_id          = $user_id;
        $exchange->charge           = $charge;
        $exchange->from_amount      = $request->amount;
        $exchange->to_amount        = $finalAmount;
        $exchange->save();
        //return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success']);

        //@mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($exchange->created_at)],$user_id);

        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money exchanged successfully.']);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    /************End Exchange API***************/

    public function transactions(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $transactions = Transaction::whereUserId($user_id)->orderBy('id','desc')->paginate(10);
            foreach ($transactions as $key => $transaction) {
                $transaction->currency = Currency::whereId($transaction->currency_id)->first();
            }
            $data['transactions'] = $transaction;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money exchanged successfully.', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
