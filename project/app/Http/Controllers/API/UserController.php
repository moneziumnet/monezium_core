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
use App\Models\BalanceTransfer;
use App\Models\DepositBank;
use App\Models\BankPlan;
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
use Request as facade_request;

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
            $def_currency = Currency::findOrFail(defaultCurr());
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$def_currency->code);
            $rate = json_decode($response->getBody());

            // $deposits = DepositBank::where('status', 'complete')->where('user_id', auth()->id())->get();
            $deposits = Transaction::where('remark', 'deposit')->orWhere('remark', 'Deposit')->where('user_id', auth()->id())->get();

            $deposit_balance = 0;
            foreach ($deposits as $value) {
                $currency = Currency::findOrFail($value->currency_id)->code;
                $deposit_balance = $deposit_balance + $value->amount / $rate->data->rates->$currency;
            }


            // $withdraws = BalanceTransfer::where('status', 1)->where('user_id', auth()->id())->where('type', 'other')->get();
            $withdraws = Transaction::where('remark', 'withdraw')->where('user_id', auth()->id())->get();
            $withdraw_balance = 0;
            foreach ($withdraws as $value) {
                $currency = Currency::findOrFail($value->currency_id)->code;
                $withdraw_balance = $withdraw_balance + $value->amount / $rate->data->rates->$currency;
            }

            $data['depositAmount'] = $deposit_balance;
            $data['withdrawAmount'] = $withdraw_balance;
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


            // $deposits = DepositBank::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus('complete')->where('user_id', auth()->id())
            $deposits = Transaction::select('id', 'updated_at', 'amount', 'currency_id' )->where('remark', 'deposit')->orWhere('remark', 'Deposit')->where('user_id', auth()->id())
            ->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->updated_at)->format('Y-m'); // grouping by months
                });
            // $withdraws = BalanceTransfer::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus(1)->where('user_id', auth()->id())->where('type', 'other')
            $withdraws = Transaction::select('id', 'updated_at', 'amount', 'currency_id' )->where('remark', 'withdraw')->where('user_id', auth()->id())
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->updated_at)->format('Y-m'); // grouping by months
            });
            $yms = array();
            $now = date('Y-m');
            for($x = 12; $x >= 0; $x--) {
                $ym = date('Y-m', strtotime($now . " -$x month"));
                array_push($yms, $ym);
            }
            $amount = [];
            $amount_w = [];
            $array_months = [];
            $array_deposits = [];
            $array_withdraws = [];
            foreach ($deposits as $key => $value) {
                $amount[$key] = 0;
                foreach($value as $deposit) {
                    $currency = Currency::findOrFail($deposit->currency_id)->code;
                    $amount[$key] = $amount[$key] + $deposit->amount / $rate->data->rates->$currency;
                }
                array_push($array_months, $key);
                $array_deposits[$key] = round($amount[$key], 2);
            }
            foreach ($withdraws as $key => $value) {
                $amount_w[$key] = 0;
                foreach($value as $withdraw) {
                    $currency = Currency::findOrFail($withdraw->currency_id)->code;
                    $amount_w[$key] = $amount_w[$key] + $withdraw->amount / $rate->data->rates->$currency;
                }
                array_push($array_months, $key);
                $array_withdraws[$key] = round($amount_w[$key], 2);
            }
            $deposit_list = [];
            $withdraw_list = [];

            foreach($yms as $month) {
                if(!array_key_exists($month, $array_deposits)) {
                    $deposit_list[$month] = 0;
                }
                else {
                    $deposit_list[$month] = $array_deposits[$month];
                }
                if(!array_key_exists($month, $array_withdraws)) {
                    $withdraw_list[$month] = 0;
                }
                else {
                    $withdraw_list[$month] = $array_withdraws[$month];
                }
            }
            ksort($deposit_list);
            ksort($withdraw_list);
            $data['array_months'] = implode(",",array_unique($yms));
            $data['array_deposits'] = implode(",",array_values($deposit_list));
            $data['array_withdraws'] = implode(",",array_values($withdraw_list));
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
            $type = auth()->user()->company_name ? 'corporate' : 'private';
            $data['packages'] = BankPlan::where('type', $type)->orderBy('amount', 'asc')->get();
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
        $remark_list = Transaction::where('user_id', auth()->id())->orderBy('remark', 'asc')->pluck('remark')->map(function ($item) {
            return ucfirst($item);
        });
        $data['remark_list'] = array_unique($remark_list->all());

        $wallet_list = Transaction::where('user_id',auth()->id())->pluck('wallet_id');
        $data['wallet_list'] = array_unique($wallet_list->all());
        $data['search'] = $search;
        $data['e_time'] = $e_time;
        $data['s_time'] = $s_time;

        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
    }

    public function trxDetails($id)
    {
        $data['user'] = Auth::user();
        $data['transaction'] = Transaction::where('id',$id)->whereUserId(auth()->id())->with('currency')->first();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
    }


    public function sendToMail(Request $request)
    {
        $gs = Generalsetting::first();
        $tran = Transaction::where('id',$request->trx_id)->whereUserId(auth()->id())->first();


        $to = $request->email;
        $subject = "Transaction Detail";

        $msg_body = '
        <!DOCTYPE html>
        <html>
        <head>
        <style>
        table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }

        td, th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }

        tr:nth-child(even) {
          background-color: #dddddd;
        }
        </style>
        </head>
        <body>

        <h2>Transaction Detail</h2>
        <p> Hello '.(auth()->user()->company_name ?? auth()->user()->name).'.</p>
        <p> This is Transacton Detail.</p>
        <p> Please confirm current.</p>

        <table>
          <tr>
            <th style="width:15%;font-size:8px;">Date/Transaction No.</th>
            <th style="width:15%;font-size:8px;">Sender</th>
            <th style="width:15%;font-size:8px;">Receiver</th>
            <th style="width:30%;font-size:8px;">Description</th>
            <th style="width:15%;font-size:8px;">Amount</th>
            <th style="width:10%;font-size:8px;">Fee</th>
          </tr>
          <tr>
            <td style="font-size:8px;">'.date('d-m-Y', strtotime($tran->created_at)).' <br/> '.$tran->trnx.'</td>
            <td style="font-size:8px;">'.(json_decode($tran->data)->sender ?? "").'</td>
            <td style="font-size:8px;">'.(json_decode($tran->data)->receiver ?? "").'</td>
            <td style="text-align: left; font-size:8px;">'.(json_decode($tran->data)->description ?? "").'<br/>'.ucwords(str_replace('_',' ',$tran->remark)).'</td>
            <td style="text-align: left;font-size:8px;">'.$tran->type.' '.amount($tran->amount,$tran->currency->type,2).' '.$tran->currency->code.' </td>
            <td style="text-align: left;font-size:8px;">- '.amount($tran->charge,$tran->currency->type,2).' '.$tran->currency->code.' </td>
          </tr>

        </table>

        </body>
        </html>

        ';


        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        $user = Auth::user();
        $transaction = Transaction::where('id',$request->trx_id)->whereUserId(auth()->id())->get();
        $image = public_path('assets/images/'.$gs->logo);
        $image_encode = base64_encode(file_get_contents($image));
        $data = [
            'trans' => $transaction,
            'user'  => $user,
            'image' => $image_encode
        ];

        $pdf = PDF::loadView('frontend.myPDF', $data);
        $folderPath = 'assets/pdf/';
        $file = $folderPath.Str::random(8).time().'.pdf';
        file_put_contents($file, $pdf->output());

        sendMail($to,$subject,$msg_body,$headers,$file);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'This transaction\'s detail has been sent to your email.']);

    }

    public function profile()
    {
        $data['user'] = Auth::user();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=>$data]);
    }

    public function profileupdate(Request $request)
    {
        $rules = [
            'photo' => 'mimes:jpeg,jpg,png,svg',
            'email' => 'unique:users,email,'.Auth::user()->id
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
        }

        $input = $request->all();

        $input['phone'] = preg_replace("/[^0-9]/", "", $request->phone);

        if(!isset(auth()->user()->company_name)) {
            $input['personal_code'] = $request->personal_code;
            $input['your_id'] = $request->your_id;
            $input['issued_authority'] = $request->issued_authority;
            $input['date_of_issue'] = $request->date_of_issue;
            $input['date_of_expire'] = $request->date_of_expire;
            $input['company_address'] = null;
            $input['company_name'] = null;
            $input['company_reg_no'] = null;
            $input['company_vat_no'] = null;
            $input['company_dob'] = null;
            $input['company_type'] = null;
            $input['company_city'] = null;
            $input['company_country'] = null;
            $input['company_zipcode'] = null;
        } else {
            $input['company_name'] = $request->company_name;
            $input['company_reg_no'] = $request->company_reg_no;
            $input['company_vat_no'] = $request->company_vat_no;
            $input['company_dob'] = $request->company_dob;
            $input['company_type'] = $request->company_type;
            $input['company_city'] = $request->company_city;
            $input['company_zipcode'] = $request->company_zipcode;
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
        mailSend('profile_udpate',[], auth()->user());
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully updated your profile']);
    }


    public function createTwoFactor(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'key' => 'required',
            'code' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
        }

        $ga = new GoogleAuthenticator();
        $secret = $request->key;
        $oneCode = $ga->getCode($secret);

        if ($oneCode == $request->code) {
            $user->go = $request->key;
            $user->twofa = 1;
            $user->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Google Two factor authentication activated']);
        } else {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your code is not matched, please input again.']);
        }
    }

    public function username_by_email(Request $request){
        if($data = User::where('email',$request->email)->first()){

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ["name" => $data->company_name ?? $data->name, "phone" => $data->phone]]);
        }else{
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'User dose not exist']);
        }
    }

    public function username_by_phone(Request $request){
        if($data = User::where('phone',preg_replace("/[^0-9]/", "", $request->phone))->first()){
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ["name" => $data->company_name ?? $data->name, "email" => $data->email]]);
        }else{
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'User dose not exist']);
        }
    }

    public function userlist_by_phone(Request $request){
        $phone_number = preg_replace("/[^0-9]/", "", $request->input('query'));
        $data = User::where('phone', 'like', '%'.$phone_number.'%')->get();
        if(count($data) > 0){
            $suggestions = array();
            foreach($data as $item) {
                array_push($suggestions, [
                    "value" => $item->phone,
                    "data" => [
                        "name" => $item->company_name ?? $item->name,
                        "email" => $item->email,
                    ],
                ]);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => [
                "query" => $request->input('query'),
                "suggestions" => $suggestions
            ]]);

        }else{
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => [
                "query" => $request->input('query'),
                "suggestions" => []
            ]]);

        }
    }

    public function security()
    {
        $user = auth()->user();
        $gnl = Generalsetting::first();
        $ga = new GoogleAuthenticator();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->name . '@' . $gnl->title, $secret);
        $data['user'] = $user;
        $data['qrCodeUrl'] = $qrCodeUrl;
        $data['secret'] = $secret;
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
    }

    public function securitystore(Request $request)
    {
        $user = auth()->user();

        $login_fa_yn = $payment_fa_yn = 'N';
        $login_fa = $payment_fa = $otp_payment = '';
        if($request->input('login_fa_yn') == 'Y')
        {
            $rules = [
                'login_fa'   => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Select 2FA Option for Login']);

            }
            $login_fa_yn = 'Y';
            $login_fa = $request->input('login_fa');
            if($login_fa == 'two_fa_google' && $user->twofa != 1){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please enable Google Two factor authentication.']);
            }
        }

        if($request->input('payment_fa_yn') == 'Y')
        {
            $rules = [
                'payment_fa'   => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Select 2FA Option for Payments']);
            }
            $payment_fa_yn = 'Y';
            $payment_fa = $request->input('payment_fa');
            if (!empty($request->otp_payment)) {
                $otp_payment = implode(" , ", $request->otp_payment);
            } else {
                $otp_payment = '';
            }
            if($payment_fa == 'two_fa_google' && $user->twofa != 1){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please enable Google Two factor authentication.']);
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
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => '2FA Features Successfully Updated']);
        }else{
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => '2FA Features Not Updated']);
        }

    }

    public function moduleupdate(Request $request) {
        $user = auth()->user();
        if (!empty($request->section)) {
            $input['modules'] = implode(" , ", $request->section);
        } else {
            $input['modules'] = '';
        }

        $status = $user->update($input);
        if($status) {
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'User Modules Updated Successfully.']);
        }else{
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'User Modules not Updated.']);
        }

    }

    public function aml_kyc() {
        $KycForms = KycRequest::where('user_id',auth()->id())->whereIn('status', [0, 2])->first();
        $informations = [];
        if ($KycForms) {
            $informations = json_decode($KycForms->kyc_info,true);
        }
        $data['KycForms'] = $KycForms;
        $data['informations'] = $informations;
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);

    }

    public function aml_kyc_store(Request $request) {
        $KycForms = KycRequest::where('id',$request->id)->first();
        $informations = json_decode($KycForms->kyc_info,true);


        $requireInformations = [];
        foreach($informations as $key=>$value){
            if($value['type'] == 'Input'){
                $requireInformations['text'][$value['label']] = strtolower(str_replace(' ', '_', $value['label']));
            }
            elseif($value['type'] == 'Textarea'){
                $requireInformations['textarea'][$value['label']] = strtolower(str_replace(' ', '_', $value['label']));
            }else{
                $requireInformations['file'][$value['label']] = strtolower(str_replace(' ', '_', $value['label']));
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
        // $details['type'] = $request->type;
        if(!empty($details)){
            $KycForms->submit_info = json_encode($details,true);
            $KycForms->submitted_date = date('Y-m-d H:i:s');
            $KycForms->status = 3;
        }
        $KycForms->save();
        send_notification(auth()->id(), 'KYC/AML has been submitted to Admin by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.user.kycinfo', auth()->id()));
        send_staff_telegram('KYC/AML has been submitted to Admin by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.user.kycinfo', auth()->id()), 'AML/KYC');
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'KYC submitted successfully']);
    }


    public function aml_kyc_history(){
        $data['history'] = KycRequest::where('user_id', auth()->id())->latest()->paginate(15);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
    }

    public function history()
    {
        $data['history'] = LoginActivity::where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(20);
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
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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

    public function register(Request $request, $id)
    {
        try {
            $rules = [
                'email'   => 'required|email|unique:users',
                'phone' => 'required',
                'password' => 'required||min:6|confirmed'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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

            $currency = Currency::findOrFail(defaultCurr());


            mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

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
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'We need to verify your email address. We have sent an email to ' . $to . ' to verify your email address. Please click link in that email to continue.']);
            } else {

                $user->email_verified = 'Yes';
                $user->update();

                $activity = new LoginActivity();
                $activity->subject = 'User Register Successfully.';
                $activity->url = facade_request::fullUrl();
                $activity->ip = $request->global_ip;
                $activity->agent = facade_request::header('user-agent');
                $activity->user_id = $user->id;
                $activity->save();

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You Register Successfully']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function forgot(Request $request) {
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

                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                sendMail($request->email,$subject,$msg,$headers);
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your Password Reseted Successfully. Please Check your email for new Password.']);
            }
            else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'No Account Found With This Email.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function logout() {
        auth()->user()->tokens()->delete();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You logged out successfully.']);
    }
}
