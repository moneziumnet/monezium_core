<?php

namespace App\Http\Controllers\User;

use PDF;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\InstallmentLog;
use App\Models\UserLoan;
use App\Models\Wallet;
use App\Traits\Payout;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\SubInsBank;
use App\Models\BankGateway;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Classes\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use App\Exports\ExportTransaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon as Carbontime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        wallet_monthly_fee(auth()->id());
        $data['user'] = Auth::user();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 1)->with('currency')->get();
        $data['wallets'] = $wallets;
        $data['cryptowallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
        $data['transactions'] = Transaction::whereUserId(auth()->id())->orderBy('id','desc')->limit(5)->get();
        $data['bankaccountlist'] = BankAccount::whereUserId(auth()->id())->get();
        $data['currencies'] = Currency::where('type', 1)->where('status', 1)->get();
        $data['crypto_currencies'] = Currency::where('type', 2)->where('status', 1)->get();
        $data['subbank'] = SubInsBank::wherestatus(1)->get();

        foreach ($data['transactions'] as $key => $transaction) {
            $transaction->currency = Currency::whereId($transaction->currency_id)->first();
        }
        $data['userBalance'] = userBalance(auth()->id());
        return view('user.dashboard',$data);
    }

    public function wallet_create (Request $request) {
        $check =  Wallet::where('user_id', $request->user_id)->where('wallet_type', 1)->where('currency_id', $request->currency_id)->first();
        if($check){
            return back()->with('error', 'This wallet already exist');
        }
        $gs = Generalsetting::first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = $request->user_id;
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $request->currency_id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 1;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

        $user =  User::findOrFail($request->user_id);
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $request->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = 1;
        $trans->amount      = $chargefee->data->fixed_charge;

        $trans_wallet = get_wallet($request->user_id, 1, 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'wallet_create';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
        $trans->save();

        user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
        return back()->with('message', 'You have created new Wallet successfully.');
    }

    public function crypto_wallet_create(Request $request) {
        $check =  Wallet::where('user_id', $request->user_id)->where('wallet_type', 8)->where('currency_id', $request->crypto_currency_id)->first();
        if($check){
            return back()->with('error', 'This wallet already exist');
        }
        $currency = Currency::findOrFail($request->crypto_currency_id);
        if ($currency->code == 'BTC') {
            $address = RPC_BTC_Create('createwallet',[auth()->user()->email]);
            $keyword = auth()->user()->email;
        }
        else {
            $address = RPC_ETH('personal_newAccount',['123123']);
            $keyword = '123123';
        }
        if ($address == 'error') {
            return back()->with('error','You can not create this wallet because there is some issue in crypto node.');
        }

        $gs = Generalsetting::first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = $request->user_id;
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $request->crypto_currency_id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 8;
        $user_wallet->wallet_no = $address;
        $user_wallet->keyword = $keyword;
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();


        $user =  User::findOrFail($request->user_id);
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $request->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = 1;
        $trans->amount      = $chargefee->data->fixed_charge;

        $trans_wallet = get_wallet($request->user_id, 1, 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'wallet_create';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
        $trans->save();

        user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);

        return back()->with('message', 'You have created new Wallet successfully.');
    }

    public function gateway(Request $request) {
        $bankgateway = BankGateway::where('subbank_id', $request->id)->first();
        return $bankgateway;
    }

    public function scanQR(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if(!$user){
             return NULL;
        }
        else {
            return $user->email;
        }
    }

    public function transaction()
    {
        $user = Auth::user();
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $transactions = Transaction::where('user_id',auth()->id())
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->when($search,function($q) use($search){
            return $q->where('trnx','LIKE',"%{$search}%");
        })
        ->whereBetween('created_at', [$s_time, $e_time])
        ->with('currency')->latest()->paginate(20);
        $remark_list = Transaction::where('user_id',auth()->id())->pluck('remark');
        $remark_list = array_unique($remark_list->all());
        return view('user.transactions',compact('user','transactions', 'search', 'remark_list', 's_time', 'e_time'));
    }

    public function transactionExport()
    {
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');

        return Excel::download( new ExportTransaction($search, $remark, $s_time, $e_time), 'transaction.xlsx');
        // foreach ($transactions as $key => $transaction) {
        //     $transaction->currency = Currency::whereId($transaction->currency_id)->first();
        // }

        // return view('user.transactions',compact('user','transactions'));
    }

    public function trxDetails($id)
    {
        $user = Auth::user();
        $transaction = Transaction::where('id',$id)->whereUserId(auth()->id())->first();
        $transaction->currency = Currency::whereId($transaction->currency_id)->first();
        if(!$transaction){
            return response('empty');
        }
        return view('user.trx_details',compact('user','transaction'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile',compact('user'));
    }

    public function profileupdate(Request $request)
    {
        $request->validate([
            'photo' => 'mimes:jpeg,jpg,png,svg',
            'email' => 'unique:users,email,'.Auth::user()->id
        ]);

        $input = $request->all();

        if($request->form_select == 0) {
            $input['personal_code'] = $request->personal_code;
            $input['your_id'] = $request->your_id;
            $input['issued_authority'] = $request->issued_authority;
            $input['date_of_issue'] = $request->date_of_issue;
            $input['date_of_expire'] = $request->date_of_expire;
            $input['address'] = $request->address;
            $input['company_name'] = null;
            $input['company_reg_no'] = null;
            $input['company_vat_no'] = null;
            $input['company_dob'] = null;
        } else {
            $input['company_name'] = $request->company_name;
            $input['company_reg_no'] = $request->company_reg_no;
            $input['company_vat_no'] = $request->company_vat_no;
            $input['company_dob'] = $request->company_dob;
            $input['address'] = $request->company_address;
            $input['personal_code'] = null;
            $input['your_id'] = null;
            $input['issued_authority'] = null;
            $input['date_of_issue'] = null;
            $input['date_of_expire'] = null;
        }

        $input['name'] = trim($request->firstname)." ".trim($request->lastname);
        $data = Auth::user();
        if ($file = $request->file('photo'))
        {
            $name = Str::random(8).time().$file->getClientOriginalExtension();
            $file->move('assets/images/',$name);
            @unlink('assets/images/'.$data->photo);

            $input['photo'] = $name;

            $input['is_provider'] = 0;
        }

        if ($file = $request->file('signature'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            @unlink('assets/images/'.$data->signature);
            $data['signature'] = $name;
        }

        if ($file = $request->file('stamp'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            @unlink('assets/images/'.$data->stamp);
            $data['stamp'] = $name;
        }

        $data->update($input);
        $msg = 'Successfully updated your profile';
        return redirect()->back()->with('success',$msg);
    }

    public function changePasswordForm()
    {
        return view('user.changepassword');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if ($request->cpass){
            if (Hash::check($request->cpass, $user->password)){
                if ($request->newpass == $request->renewpass){
                    $input['password'] = Hash::make($request->newpass);
                }else{
                    return redirect()->back()->with('unsuccess','Confirm password does not match.');
                }
            }else{
                return redirect()->back()->with('unsuccess','Current password Does not match.');
            }
        }
        $user->update($input);
        return redirect()->back()->with('success','Password Successfully Changed.');
    }

    public function showTwoFactorForm()
    {
        $gnl = Generalsetting::first();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->name . '@' . $gnl->title, $secret);
        $prevcode = $user->tsc;
        $prevqr = $ga->getQRCodeGoogleUrl($user->name . '@' . $gnl->title, $prevcode);

        return view('user.twofactor.index', compact('secret', 'qrCodeUrl', 'prevcode', 'prevqr'));
    }

    public function createTwoFactor(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);

        $ga = new GoogleAuthenticator();
        $secret = $request->key;
        $oneCode = $ga->getCode($secret);

        if ($oneCode == $request->code) {
            $user->go = $request->key;
            $user->twofa = 1;
            $user->save();

            return redirect()->back()->with('success','Two factor authentication activated');
        } else {
            return redirect()->back()->with('error','Something went wrong!');
        }
    }


    public function disableTwoFactor(Request $request)
    {

        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = auth()->user();
        $ga = new GoogleAuthenticator();

        $secret = $user->go;
        $oneCode = $ga->getCode($secret);
        $userCode = $request->code;

        if ($oneCode == $userCode) {

            $user->go = null;
            $user->twofa = 0;

            $user->save();

            return redirect()->back()->with('success','Two factor authentication disabled');
        } else {
            return redirect()->back()->with('error','Something went wrong!');
        }
    }

    public function username($number){
       if($data = User::where('account_number',$number)->first()){
           return $data->name;
       }else{
           return false;
       }
    }

    public function username_by_email(Request $request){
        if($data = User::where('email',$request->email)->first()){
            return $data;
        }else{
            return false;
        }
     }

    public function generatePDF()
    {
        $data = [
            'title' => 'Welcome to geniusbank',
            'date' => date('m/d/Y')
        ];

        $pdf = PDF::loadView('frontend.myPDF', $data);

        return $pdf->download('transaction.pdf');
    }

    public function transactionPDF()
    {
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');
        return Excel::download( new ExportTransaction($search, $remark, $s_time, $e_time), 'transaction.pdf',\Maatwebsite\Excel\Excel::DOMPDF);
    }

    public function affilate_code()
    {
        $user = Auth::guard('web')->user();
        return view('user.affilate_code',compact('user'));
    }

    public function securityform(Request $request)
    {
        $user = auth()->user();

        if($request->isMethod('post'))
        {

            $login_fa_yn = $payment_fa_yn = 'N';
            $login_fa = $payment_fa = $otp_payment = '';
            if($request->input('login_fa_yn') == 'Y')
            {
                $rules = [
                    'login_fa'   => 'required'
                ];
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return redirect()->back()->with('unsuccess','Select 2FA Option for Login');
                }
                $login_fa_yn = 'Y';
                $login_fa = $request->input('login_fa');
            }

            if($request->input('payment_fa_yn') == 'Y')
            {
                $rules = [
                    'payment_fa'   => 'required'
                ];
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return redirect()->back()->with('unsuccess','Select 2FA Option for Payments');
                }
                $payment_fa_yn = 'Y';
                $payment_fa = $request->input('payment_fa');
                if (!empty($request->otp_payment)) {
                    $otp_payment = implode(" , ", $request->otp_payment);
                } else {
                    $otp_payment = '';
                }
            }

            $update = User::where('id', $user->id)->update([
                'login_fa_yn'  => $login_fa_yn,
                'login_fa'     => $login_fa,
                'payment_fa_yn'=> $payment_fa_yn,
                'otp_payments'=> $otp_payment,
                'payment_fa'=> $payment_fa
            ]);

            if($update)
            {
                return redirect()->back()->with('success','2FA Features Successfully Updated');
            }else{
                return redirect()->back()->with('unsuccess','2FA Features Not Updated');
            }

        }

        return view('user.security.index', compact('user'));
    }

    public function installmentCheck(){
        $loans = UserLoan::whereStatus(1)->get();
        $now = Carbontime::now();

        foreach($loans as $key=>$data){
          if($data->given_installment == $data->total_installment){
            return false;
          }
          if($now->gt($data->next_installment)){
            $this->takeLoanAmount($data->user_id,$data->per_installment_amount, $data);
            $this->logCreate($data->transaction_no,$data->per_installment_amount,$data->user_id);

            $data->next_installment = Carbontime::now()->addDays($data->plan->installment_interval);
            $data->given_installment += 1;
            $data->paid_amount += $data->per_installment_amount;
            $data->update();

            if($data->given_installment == $data->total_installment){
              $this->paid($data);
            }
          }
        }
      }

      public function takeLoanAmount($userId,$installment, $data){
        $user = User::whereId($userId)->first();
        $currency = $data->currency->id;
        $userBalance = user_wallet_balance($user->id, $currency, 4);
        if($user && $userBalance>=$installment){
          user_wallet_decrement($user->id, $currency, $installment, 4);
        }
      }

      public function paid($loan){
        $loan = UserLoan::whereId($loan->id)->first();
        if($loan){
            $loan->status = 3;
            $loan->next_installment = NULL;
            $loan->update();
        }
      }


      public function logCreate($transactionNo,$amount,$userId){
        $data = new InstallmentLog();
        $data->user_id = $userId;
        $data->transaction_no = $transactionNo;
        $data->type = 'loan';
        $data->amount = $amount;
        $data->save();
      }


}
