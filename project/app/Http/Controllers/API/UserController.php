<?php

namespace App\Http\Controllers\API;

use PDF;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Charge;
use App\Models\Wallet;
use App\Traits\Payout;
use App\Models\Currency;
use App\Models\UserLoan;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\Transaction;
use App\Models\KycRequest;
use App\Models\LoginActivity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\InstallmentLog;
use App\Exports\ExportTransaction;
use App\Classes\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Carbon as Carbontime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Completion\Suggestion;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            abort(response()->json('The provided credentials are incorrect.', 401));
        }

        $accessToken=$user->createToken($request->device_name)->plainTextToken;
        return response(['access_token'=>$accessToken, 'user'=>$user]);
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
                $msg = "Dear Customer,<br> We noticed that you need to verify your email address. <br>" . $verificationLink;


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
                        $trans_wallet = get_wallet($mainUser->id, $currency);
                        $mainUserTrans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
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
                        $trans_wallet = get_wallet($user->id, $currency);
                        $newUserTrans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
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
                sendMail($request->email,$subject,$msg,$headers);
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
            wallet_monthly_fee(auth()->id());
            $gs = Generalsetting::first();
            $data['user'] = Auth::user();
            $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 1)->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['cryptowallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
            $data['transactions'] = Transaction::whereUserId(auth()->id())->orderBy('id','desc')->limit(5)->get();
            $data['bankaccountlist'] = BankAccount::whereUserId(auth()->id())->get();
            $data['currencies'] = Currency::where('type', 1)->where('status', 1)->get();
            $data['crypto_currencies'] = Currency::where('type', 2)->where('status', 1)->get();
            $data['subbank'] = SubInsBank::wherestatus(1)->get();
            $data['kyc_request_id'] = 0;
            $data['kyc_request_status'] = 4;
            $kycrequest = KycRequest::where('user_id', auth()->id())->whereIn('status', [0, 2])->first();
            if($kycrequest){
                $data['kyc_request_status'] = $kycrequest->status;
                $data['kyc_request_id'] = $kycrequest->id;
            }
            else {
                $kycrequest = KycRequest::where('user_id', auth()->id())->whereIn('status', [1, 3])->first();
                if ($kycrequest) {
                    $data['kyc_request_status'] = $kycrequest->status;
                    $data['kyc_request_id'] = $kycrequest->id;
                }
            }

            foreach ($data['transactions'] as $key => $transaction) {
                $transaction->currency = Currency::whereId($transaction->currency_id)->first();
            }
            $data['userBalance'] = userBalance(auth()->id());
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function wallet_create (Request $request) {
        $check =  Wallet::where('user_id', auth()->id())->where('wallet_type', 1)->where('currency_id', $request->currency_id)->first();
        if($check){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This wallet already exist.']);
        }
        $gs = Generalsetting::first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = auth()->id();
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $request->currency_id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 1;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

        $user =  User::findOrFail(auth()->id());
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = auth()->id();
        $trans->user_type   = 1;
        $trans->currency_id = defaultCurr();
        $trans->amount      = 0;

        $trans_wallet = get_wallet(auth()->id(), defaultCurr(), 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();


        $def_currency = Currency::findOrFail(defaultCurr());
        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have created new Wallet successfully.']);
    }


    public function crypto_wallet_create(Request $request) {
        $check =  Wallet::where('user_id', auth()->id())->where('wallet_type', 8)->where('currency_id', $request->crypto_currency_id)->first();
        if($check){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This wallet already exist.']);
        }
        $currency = Currency::findOrFail($request->crypto_currency_id);
        if ($currency->code == 'BTC') {
            $keyword = str_rand();
            $address = RPC_BTC_Create('createwallet',[$keyword]);
        }
        else if ($currency->code == 'ETH'){
            $keyword = str_rand(6);
            $address = RPC_ETH('personal_newAccount',[$keyword]);
        } else {
            $eth_currency = Currency::where('code', 'ETH')->first();
            $eth_wallet = Wallet::where('user_id', auth()->id())->where('wallet_type', 8)->where('currency_id', $eth_currency->id)->first();
            if (!$eth_wallet) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to create Eth Crypto wallet firstly before create ERC20 token wallet.']);
            }
            $address = $eth_wallet->wallet_no;
            $keyword = $eth_wallet->keyword;
        }
        if ($address == 'error') {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not create this wallet because there is some issue in crypto node.']);
        }

        $gs = Generalsetting::first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = auth()->id();
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $request->crypto_currency_id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 8;
        $user_wallet->wallet_no = $address;
        $user_wallet->keyword = $keyword;
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();


        $user =  User::findOrFail(auth()->id());
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }
        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = auth()->id();
        $trans->user_type   = 1;
        $trans->currency_id = defaultCurr();
        $trans->amount      = 0;

        $trans_wallet = get_wallet(auth()->id(), defaultCurr(), 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();

        $def_currency = Currency::findOrFail(defaultCurr());
        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Crypto', 'date_time'=> dateFormat($trans->created_at)], $user);

        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have created new Wallet successfully.']);
    }

    public function gateway(Request $request) {
        $bankgateway = BankGateway::where('subbank_id', $request->id)->first();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $bankgateway]);
    }

    public function packages(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['packages'] = BankPlan::orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function scanQR(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if(!$user){
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => NULL]);
        }
        else {
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $user->email]);
        }
    }

    public function transaction()
    {
        $data['user'] = Auth::user();
        $search = request('search');
        $remark = request('remark');
        $wallet_id = request('wallet_id');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $data['transactions'] = Transaction::where('user_id',auth()->id())
        ->when($wallet_id,function($q) use($wallet_id){
            return $q->where('wallet_id',$wallet_id);
        })
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->when($search,function($q) use($search){
            return $q->where('trnx','LIKE',"%{$search}%");
        })
        ->whereBetween('created_at', [$s_time, $e_time])
        ->orderBy('created_at', 'desc')
        ->with('currency')->latest()->paginate(20);
        $remark_list = Transaction::where('user_id',auth()->id())->pluck('remark');
        $data['remark_list'] = array_unique($remark_list->all());

        $wallet_list = Transaction::where('user_id',auth()->id())->pluck('wallet_id');
        $data['wallet_list'] = array_unique($wallet_list->all());
        $data['search'] = $search;
        $data['e_time'] = $e_time;
        $data['s_time'] = $s_time;

        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
    }

    public function changepassword(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

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
            $user_id = Auth::user()->id;
            $data['tickets'] = AdminUserConversation::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

}
