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
use App\Models\Withdrawals;
use App\Models\Currency;
use App\Models\Beneficiary;
use App\Models\OtherBank;
use App\Models\UserLoan;
use App\Models\UserDps;
use App\Models\DpsPlan;
use App\Models\LoanPlan;
use App\Models\Deposit;
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
use App\Models\DepositBank;
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



    

    
    
    
    
    
    

    
    
   

    
}
