<?php

namespace App\Http\Controllers\User;

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
use App\Models\ActionNotification;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        wallet_monthly_fee(auth()->id());
        $gs = Generalsetting::first();
        $data['user'] = Auth::user();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 1)->with('currency')->get();
        $data['wallets'] = $wallets;
        $data['cryptowallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
        $data['transactions'] = Transaction::whereUserId(auth()->id())->orderBy('id','desc')->limit(15)->get();
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

        $deposits = Transaction::where('remark', 'deposit')->where('user_id', auth()->id())->orWhere('remark', 'Deposit')->where('user_id', auth()->id())->get();
        $deposit_balance = 0;
        foreach ($deposits as $value) {
            $currency = Currency::findOrFail($value->currency_id)->code;
            $deposit_balance = $deposit_balance + $value->amount / $rate->data->rates->$currency;
        }


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
        if (request('state')) {
            $client = New Client();
            $access_token = Session::get('Swan_token');
            $subbank = Session::get('subbank');
            $currency_id = Session::get('currency');
            try {
                $body = '{"query":"query MyQuery {\\n  onboarding(id: \\"'.request('state').'\\") {\\n    id\\n    account {\\n      BIC\\n      IBAN\\n      balances {\\n        available {\\n          currency\\n          value\\n        }\\n      }\\n      id\\n    }\\n  }\\n}","variables":{}}';
                $headers = [
                    'Authorization' => 'Bearer '.$access_token,
                    'Content-Type' => 'application/json'
                  ];
                $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                    'body' => $body,
                    'headers' => $headers
                ]);
                $res_body = json_decode($response->getBody());

                $iban = $res_body->data->onboarding->account->IBAN ?? '';
                $bic_swift = $res_body->data->onboarding->account->BIC ?? '';
            } catch (\Throwable $th) {
                return redirect()->route('user.dashboard')->with(array('warning' => json_encode($th->getMessage())));
            }
            $user = auth()->user();
            $bankaccount = New BankAccount();
            $bankaccount->user_id = auth()->id();
            $bankaccount->subbank_id = $subbank;
            $bankaccount->iban = $iban;
            $bankaccount->swift = $bic_swift;
            $bankaccount->currency_id = $currency_id;
            $bankaccount->save();

            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if(!$chargefee) {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id =  defaultCurr();
            $trans->amount      = 0;
            $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = $chargefee->data->fixed_charge;
            $trans->type        = '-';
            $trans->remark      = 'account-open';
            $trans->details     = trans('Bank Account Create');
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
            $trans->save();

            $def_currency = Currency::findOrFail(defaultCurr());
            mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);
            send_notification($user->id, 'New Bank Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-banks', $user->id));
            
            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            return redirect()->route('user.dashboard')->with(array('message' => 'Bank Account has been created successfully.'));
        }

        $deposits = Transaction::select('id', 'updated_at', 'amount', 'currency_id' )->where('remark', 'deposit')->where('user_id', auth()->id())->orWhere('remark', 'Deposit')->where('user_id', auth()->id())
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->updated_at)->format('Y-m'); // grouping by months
            });
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
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $request->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = defaultCurr();
        $trans->amount      = 0;

        $trans_wallet = get_wallet($request->user_id, defaultCurr(), 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();


        $def_currency = Currency::findOrFail(defaultCurr());
        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

        send_notification($user->id, 'New Current Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        return back()->with('message', 'You have created new Wallet successfully.');
    }

    public function crypto_wallet_create(Request $request) {
        $check =  Wallet::where('user_id', $request->user_id)->where('wallet_type', 8)->where('currency_id', $request->crypto_currency_id)->first();
        if($check){
            return back()->with('error', 'This wallet already exist');
        }
        $currency = Currency::findOrFail($request->crypto_currency_id);
        if ($currency->code == 'BTC') {
            $keyword = str_rand();
            $address = RPC_BTC_Create('createwallet',[$keyword]);
        }
        else if ($currency->code == 'ETH'){
            $keyword = str_rand(6);
            $address = RPC_ETH('personal_newAccount',[$keyword]);
        } elseif ($currency->code == 'TRON') {
            $addressData = RPC_TRON_Create();
            $address = $addressData->address;
            $keyword = $addressData->privateKey;
        }else {
            $eth_currency = Currency::where('code', 'ETH')->first();
            $eth_wallet = Wallet::where('user_id', $request->user_id)->where('wallet_type', 8)->where('currency_id', $eth_currency->id)->first();
            if (!$eth_wallet) {
                return back()->with('error','You have to create Eth Crypto wallet firstly before create ERC20 token wallet.');
            }
            $address = $eth_wallet->wallet_no;
            $keyword = $eth_wallet->keyword;
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
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }
        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $request->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = defaultCurr();
        $trans->amount      = 0;

        $trans_wallet = get_wallet($request->user_id, defaultCurr(), 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();

        $def_currency = Currency::findOrFail(defaultCurr());
        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Crypto', 'date_time'=> dateFormat($trans->created_at)], $user);
        send_notification($user->id, 'New Crypto Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

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
        $wallet_id = request('wallet_id');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $transactions = Transaction::where('user_id',auth()->id())
        // ->where('wallet_id', $wallet_id)
        ->when($wallet_id,function($q) use($wallet_id){
            return $q->where('wallet_id',$wallet_id);
        })
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->whereBetween('created_at', [$s_time, $e_time])
        ->orderBy('created_at', 'desc')
        ->with('currency')->get();
        if(isset($search)) {
            $transactions = $transactions->filter(function ($item) use($search) {
                return (stripos(strtolower($item->data), strtolower($search)) !== false) || (stripos(strtolower($item->trnx), strtolower($search)) !== false) || (stripos(strtolower($item->details), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false) || (stripos(strtolower(round($item->charge, 2)), strtolower($search)) !== false);
            });
        }

        $transactions = new LengthAwarePaginator(
            $transactions->forPage(request()->input('page', 1), 20),
            $transactions->count(),
            20,
            request()->input('page', 1)
        );

        $transactions->withPath(request()->url());
 
        $remark_list = Transaction::where('user_id', auth()->id())->orderBy('remark', 'asc')->pluck('remark')->map(function ($item) {
            return ucfirst($item);
        });
        $remark_list = array_unique($remark_list->all());

        $wallet_list = Transaction::where('user_id',auth()->id())->pluck('wallet_id');
        $wallet_list = array_unique($wallet_list->all());

        return view('user.transactions',compact('user','transactions', 'search', 'remark_list', 's_time', 'e_time', 'wallet_list'));
    }

    public function transactionExport()
    {
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $wallet_id = request('wallet_id');
        $date = Carbontime::now();
        $xls_name = $date->format('mdYHis').'_transaction.xlsx';
        return Excel::download( new ExportTransaction($search, $remark, $s_time, $e_time,$wallet_id), $xls_name);
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

    public function trxDetails_pdf($id)
    {
        $user = Auth::user();
        $transaction = Transaction::where('id',$id)->whereUserId(auth()->id())->get();
        $gs = Generalsetting::first();
        $image = public_path('assets/images/'.$gs->footer_logo);
        $image_encode = base64_encode(file_get_contents($image));
        $data = [
            'trans' => $transaction,
            'user'  => $user,
            'image' => $image_encode,
            'wallet' => Transaction::where('id',$id)->first()->wallet
        ];

        $pdf = PDF::loadView('frontend.myPDF', $data);
        foreach ($transaction as $key => $trans) {
            $pdf_name = date('mdY', strtotime($trans->created_at)).'_'.$trans->trnx.'.pdf';
        }
        return $pdf->download($pdf_name);
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
            <th style="width:15%;font-size:8px;">Date / Transaction ID</th>
            <th style="width:15%;font-size:8px;">Sender</th>
            <th style="width:15%;font-size:8px;">Receiver</th>
            <th style="width:30%;font-size:8px;">Description</th>
            <th style="width:15%;font-size:8px;">Amount</th>
            <th style="width:10%;font-size:8px;">Fee</th>
          </tr>
          <tr>
            <td style="font-size:8px;">'.date('d-M-Y', strtotime($tran->created_at)).' <br/> '.$tran->trnx.'</td>
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
        $image = public_path('assets/images/'.$gs->footer_logo);
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

        // More headers

        sendMail($to,$subject,$msg_body,$headers,$file);



        return back()->with('message','This transaction\'s detail has been sent to your email.');
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
        send_notification($data->id, ($data->company_name ?? $data->name).' profile is updated. Please check .', route('admin-user-profile', $data->id));

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

    public function changetheme()
    {
        return view('user.changetheme');
    }

    public function updatetheme(Request $request)
    {
        $user = Auth::user();
        $user->website_theme = $request->website_theme;
        $user->update();
        return redirect()->back()->with('success','Theme Successfully Changed.');
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

            return redirect()->back()->with('message','Google Two factor authentication activated');
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
            return ["name" => $data->company_name ?? $data->name, "phone" => $data->phone];
        }else{
            return false;
        }
    }

    public function username_by_phone(Request $request){
        if($data = User::where('phone',preg_replace("/[^0-9]/", "", $request->phone))->first()){
            return ["name" => $data->company_name ?? $data->name, "email" => $data->email];
        }else{
            return false;
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
            return json_encode([
                "query" => $request->input('query'),
                "suggestions" => $suggestions
            ]);
        }else{
            return json_encode([
                "query" => $request->input('query'),
                "suggestions" => []
            ]);
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
        $wallet_id = request('wallet_id');
        $s_time = $s_time ? $s_time : '';
        $e_time = request('e_time');

        $user = Auth::user();
        $transactions = Transaction::with('currency')->whereUserId(auth()->id())
        ->when($wallet_id,function($q) use($wallet_id){
            return $q->where('wallet_id',$wallet_id);
        })
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->whereBetween('created_at', [$s_time, $e_time])
        ->orderBy('created_at','desc')->get();
        if(isset($search)) {
            $transactions = $transactions->filter(function ($item) use($search) {
                return (stripos(strtolower($item->data), strtolower($search)) !== false) || (stripos(strtolower($item->trnx), strtolower($search)) !== false) || (stripos(strtolower($item->details), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false);
            });
        }

        $s_transactions = Transaction::with('currency')->whereUserId(auth()->id())
        ->when($wallet_id,function($q) use($wallet_id){
            return $q->where('wallet_id',$wallet_id);
        })
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->when($search,function($q) use($search){
            return $q->where('trnx','LIKE',"%{$search}%");
        })
        ->whereBetween('created_at', ['', $s_time])
        ->orderBy('id','desc')->get();

        if(isset($search)) {
            $s_transactions = $s_transactions->filter(function ($item) use($search) {
                return (stripos(strtolower($item->data), strtolower($search)) !== false) || (stripos(strtolower($item->trnx), strtolower($search)) !== false) || (stripos(strtolower($item->details), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false);
            });
        }

        $e_transactions = Transaction::with('currency')->whereUserId(auth()->id())
        ->when($wallet_id,function($q) use($wallet_id){
            return $q->where('wallet_id',$wallet_id);
        })
        ->when($remark,function($q) use($remark){
            return $q->where('remark',$remark);
        })
        ->when($search,function($q) use($search){
            return $q->where('trnx','LIKE',"%{$search}%");
        })
        ->whereBetween('created_at', ['', $e_time])
        ->orderBy('id','desc')->get();
        if(isset($search)) {
            $e_transactions = $e_transactions->filter(function ($item) use($search) {
                return (stripos(strtolower($item->data), strtolower($search)) !== false) || (stripos(strtolower($item->trnx), strtolower($search)) !== false) || (stripos(strtolower($item->details), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false) || (stripos(strtolower(round($item->amount, 2)), strtolower($search)) !== false);
            });
        }
        $currency_id = defaultCurr();
        $def_code = Currency::findOrFail($currency_id)->code;
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
        $rate = json_decode($response->getBody());
        $s_balance = 0;

        foreach($s_transactions as $key => $value) {
                $code = $value->currency->code;
                if($value->type == '+') {
                    $s_balance = $s_balance + $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);
                    $s_balance = $s_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

                }
                elseif($value->type == '-' && $value->amount == 0) {
                    $s_balance = $s_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);
                }
                else {
                    $s_balance = $s_balance - $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);
                    $s_balance = $s_balance + $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

                }
        }
        $e_balance = 0;

        foreach($e_transactions as $key => $value) {
            $code = $value->currency->code;
            if($value->type == '+') {
                $e_balance = $e_balance + $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);
                $e_balance = $e_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

            }
            elseif($value->type == '-' && $value->amount == 0) {
                $e_balance = $e_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);
            }
            else {
                $e_balance = $e_balance - $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);
                $e_balance = $e_balance + $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

            }
    }


        $gs = Generalsetting::first();
        $image = public_path('assets/images/'.$gs->footer_logo);
        $image_encode = base64_encode(file_get_contents($image));
        if($wallet_id != '') {
            $wallet = Wallet::findOrFail($wallet_id);
        }
        $data = [
            'trans' => $transactions,
            'user'  => $user,
            'start_time'  => $s_time,
            'end_time'  => $e_time,
            'image' => $image_encode,
            'wallet' => isset($wallet) ? $wallet : '',
            's_bal' => amount($s_balance, Currency::findOrFail($currency_id)->type, 2),
            'e_bal' => amount($e_balance, Currency::findOrFail($currency_id)->type, 2),
            'def_code' => $def_code
        ];
        $pdf = PDF::loadView('frontend.myPDF', $data);
        $date = Carbontime::now();
        $pdf_name = $date->format('mdYHis').'_transaction.pdf';
        return $pdf->download($pdf_name);

	}


    public function affilate_code()
    {
        $user = Auth::guard('web')->user();
        return view('user.affilate_code',compact('user'));
    }

    public function securityform(Request $request)
    {
        $user = auth()->user();
        $gnl = Generalsetting::first();
        $ga = new GoogleAuthenticator();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->name . '@' . $gnl->title, $secret);

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
                if($login_fa == 'two_fa_google' && $user->twofa != 1){
                    return redirect()->back()->with('error','Please enable Google Two factor authentication.');
                }
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
                if($payment_fa == 'two_fa_google' && $user->twofa != 1){
                    return redirect()->back()->with('error','Please enable Google Two factor authentication.');
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

        return view('user.security.index', compact('user', 'qrCodeUrl', 'secret'));
    }

    public function usermodule()
    {
        $user = auth()->user();

        return view('user.module.index', compact('user'));
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
            return redirect()->back()->with('success','User Modules Updated Successfully.');
        }else{
            return redirect()->back()->with('unsuccess','User Modules not Updated.');
        }

    }

    public function aml_kyc() {
        $KycForms = KycRequest::where('user_id',auth()->id())->whereIn('status', [0, 2])->first();
        $informations = [];
        if ($KycForms) {
            $informations = json_decode($KycForms->kyc_info,true);
        }

        return view('user.aml.index',compact('KycForms', 'informations'));
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
        return redirect()->route('user.aml.kyc.history')->with('message','KYC submitted successfully');
    }

    public function aml_kyc_history(){
        $data['history'] = KycRequest::where('user_id', auth()->id())->latest()->paginate(15);
        return view('user.aml.history', $data);
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

      public function history()
      {
          $history = LoginActivity::where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(20);
          return view('user.loginactivity', compact('history'));
      }

      public function notification()
      {
          $notifications = ActionNotification::where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(20);
          $data = ActionNotification::where('user_status', '0')->update(['user_status' => '1']);
          return view('user.notifications', compact('notifications'));
      }



}
