<?php

namespace App\Http\Controllers\Admin;

use Zip;
use App\Models\Blog;
use App\Models\User;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Deposit;
use App\Models\Currency;
use App\Models\Withdraw;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\DepositBank;
use App\Models\BalanceTransfer;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\RequestDomain;
use App\Models\Withdrawals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index()
    {
        $gs = Generalsetting::first();
        $def_currency = Currency::findOrFail(defaultCurr());
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$def_currency->code);
        $rate = json_decode($response->getBody());

        if (Auth::guard('admin')->user()->IsSuper()) {
            $data['ainstitutions'] = Admin::orderBy('id', 'desc')->where('tenant_id', '!=', '')->get();
            $data['languages'] = Language::all();
            $data['adomains'] = RequestDomain::orderBy('id', 'desc')->where('is_approved', 1)->get();
        } else {

            $data['blogs'] = Blog::all();
            $data['deposits'] = Deposit::all();
            // $deposits = DepositBank::where('status', 'complete')->get();
            $deposits = Transaction::where('remark', 'deposit')->orWhere('remark', 'Deposit')->get();
            $deposit_transaction = Transaction::where('remark', 'deposit')->orWhere('remark', 'Deposit')->get();
            $deposit_balance = 0;
            $charge_balance = 0;
            foreach ($deposits as $value) {
                $currency = Currency::findOrFail($value->currency_id)->code;
                $deposit_balance = $deposit_balance + $value->amount / $rate->data->rates->$currency;
            }

            foreach ($deposit_transaction as $value) {
                $currency = Currency::findOrFail($value->currency_id)->code;
                $charge_balance = $charge_balance + $value->charge / $rate->data->rates->$currency;
            }

            // $withdraws = BalanceTransfer::where('status', 1)->where('type', 'other')->get();
            $withdraws = Transaction::where('remark', 'withdraw')->get();
            $withdraw_balance = 0;
            foreach ($withdraws as $value) {
                $currency = Currency::findOrFail($value->currency_id)->code;
                $withdraw_balance = $withdraw_balance + $value->amount / $rate->data->rates->$currency;
                $charge_balance = $charge_balance + $value->charge / $rate->data->rates->$currency;
            }

            $data['depositAmount'] = $deposit_balance;
            $data['withdrawAmount'] = $withdraw_balance;
            $data['ChargeAmount'] = $charge_balance;
            $data['currency'] = Currency::whereIsDefault(1)->first();
            $data['transactions'] = Transaction::all();
            $data['acustomers'] = User::orderBy('id', 'desc')->whereIsBanned(0)->get();
            $data['users'] = User::orderBy('id', 'desc')->limit(5)->get();
            $data['bcustomers'] = User::orderBy('id', 'desc')->whereIsBanned(1)->get();
            $data['payouts'] = Withdrawals::where('status', 'completed')->sum('amount');

            $data['activation_notify'] = "";

            // $deposits = DepositBank::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus('complete')
            $deposits = Transaction::select('id', 'updated_at', 'amount', 'currency_id' )->where('remark', 'deposit')->orWhere('remark', 'Deposit')
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->updated_at)->format('Y-m'); // grouping by months
            });
            // $withdraws = BalanceTransfer::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus(1)->where('type', 'other')
            $withdraws = Transaction::select('id', 'updated_at', 'amount', 'currency_id' )->where('remark', 'withdraw')
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


        }
        // if (file_exists(public_path().'/rooted.txt')){
        //     $rooted = file_get_contents(public_path().'/rooted.txt');
        //     if ($rooted < date('Y-m-d', strtotime("+10 days"))){
        //         $activation_notify = "<i class='icofont-warning-alt icofont-4x'></i><br>Please activate your system.<br> If you do not activate your system now, it will be inactive on ".$rooted."!!<br><a href='".url('/admin/activation')."' class='btn btn-success'>Activate Now</a>";
        //     }
        // }
        if (request('state')) {
            $client = new Client();
            $access_token = Session::get('Swan_token');
            $subbank = Session::get('subbank');
            $currency_id = Session::get('currency');
            $user_id = Session::get('user_id');
            try {
                $body = '{"query":"query MyQuery {\\n  onboarding(id: \\"' . request('state') . '\\") {\\n    id\\n    account {\\n      BIC\\n      IBAN\\n      balances {\\n        available {\\n          currency\\n          value\\n        }\\n      }\\n      id\\n    }\\n  }\\n}","variables":{}}';
                $headers = [
                    'Authorization' => 'Bearer ' . $access_token,
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
                return redirect()->route('admin.dashboard')->with(array('warning' => json_encode($th->getMessage())));
            }
            $user = User::findOrFail($user_id);
            $bank = new BankAccount();
            $bank->user_id = $user->id;
            $bank->subbank_id = $subbank;
            $bank->iban = $iban;
            $bank->swift = $bic_swift;
            $bank->currency_id = $currency_id;
            $bank->save();

            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if (!$chargefee) {
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
            $trans->data        = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
            $trans->save();
            $currency = Currency::findOrFail(defaultCurr());

            mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);

            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            return redirect()->route('admin.dashboard')->with(array('message' => $user->name . '\'s Swan Bank Account has been created successfully.'));
        }
        if (request('consentId')) {
            $currency_id = Session::get('currency_id');
            $user_id = Session::get('user_id');
            $user = User::findOrFail($user_id);
            $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if (!$chargefee) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user_id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency_id;
            $trans_wallet = get_wallet($id, $currency_id, 1);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->amount      = 0;
            $trans->charge      = $chargefee->data->fixed_charge;
            $trans->type        = '-';
            $trans->remark      = 'card-issuance';
            $trans->details     = trans('Card Issuance');
            $trans->data        = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
            $trans->save();

            $trx = 'VC-' . Str::random(6);
            $sav['user_id'] = $user->id;
            $sav['first_name'] = explode(" ", $user->name)[0];
            $sav['last_name'] = explode(" ", $user->name)[1];
            $sav['account_id'] = $user->id;
            $sav['card_hash'] = $user->id;
            $sav['card_pan'] = generate_card_number(16);
            $sav['masked_card'] = 'mc_' . rand(100, 999);
            $sav['cvv'] = rand(100, 999);
            $sav['expiration'] = '10/24';
            $sav['card_type'] = 'normal';
            $sav['name_on_card'] = 'noc_US';
            $sav['callback'] = " ";
            $sav['ref_id'] = $trx;
            $sav['secret'] = $trx;
            $sav['city'] = $user->city;
            $sav['zip_code'] = $user->zip;
            $sav['address'] = $user->address;
            $sav['wallet_id'] = $user_wallet->id;
            $sav['amount'] = 0;
            $sav['currency_id'] = $currency_id;
            $sav['charge'] = 0;
            VirtualCard::create($sav);
            $address = $gs->wallet_no_prefix . date('ydis') . random_int(100000, 999999);

            $user_wallet = new Wallet();
            $user_wallet->user_id = $user_id;
            $user_wallet->user_type = 1;
            $user_wallet->currency_id = $currency_id;
            $user_wallet->balance = 0;
            $user_wallet->wallet_type = 2;
            $user_wallet->wallet_no = $address;
            $user_wallet->created_at = date('Y-m-d H:i:s');
            $user_wallet->updated_at = date('Y-m-d H:i:s');
            $user_wallet->save();

            $currency = Currency::findOrFail(defaultCurr());

            mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Card', 'date_time'=> dateFormat($trans->created_at)], $user);

            user_wallet_decrement($user->id, $currency_id, $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, $currency_id, $chargefee->data->fixed_charge, 9);
            return redirect()->route('admin.dashboard')->with(array('message' => 'Virtual card was successfully created.'));
        }




        

        return view('admin.dashboard', $data);
    }
    public function passwordreset()
    {
        $data = Auth::guard('admin')->user();
        return view('admin.password', compact('data'));
    }

    public function changepass(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($request->cpass) {
            if (Hash::check($request->cpass, $admin->password)) {
                if ($request->newpass == $request->renewpass) {
                    $input['password'] = Hash::make($request->newpass);
                } else {
                    return response()->json(array('errors' => [0 => 'Confirm password does not match.']));
                }
            } else {
                return response()->json(array('errors' => [0 => 'Current password Does not match.']));
            }
        }
        $admin->update($input);
        $msg = 'Successfully change your password';
        return response()->json($msg);
    }

    public function profile()
    {
        $data = tenancy()->central(function ($tenant) {
            return Admin::findOrFail($tenant->id);
        });
        $data = Auth::guard('admin')->user();
        $modules = Generalsetting::first();
        return view('admin.profile', compact('data', 'modules'));
    }

    public function profileupdate(Request $request)
    {
        //--- Validation Section

        $rules =
            [
                'photo' => 'mimes:jpeg,jpg,png,svg',
                'email' => 'unique:admins,email,' . Auth::guard('admin')->user()->id
            ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();

        $data = tenancy()->central(function ($tenant) {
            return Admin::findOrFail($tenant->id);
        });
        // $data = Auth::guard('admin')->user();

        if ($file = $request->file('photo')) {
            $name = Str::random(8) . time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images/', $name);
            if ($data->photo != null) {
                if (file_exists(public_path() . '/assets/images/' . $data->photo)) {
                    unlink(public_path() . '/assets/images/' . $data->photo);
                }
            }
            $input['photo'] = $name;
        }
        $input['slug'] = str_replace(" ", "-", $input['name']);

        $data->update($input);

        $data = Auth::guard('admin')->user();
        $data->update($input);

        $msg = 'Successfully updated your profile';
        return response()->json($msg);
    }

    public function generate_bkup()
    {
        $bkuplink = "";
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            $bkuplink = url($chk);
        }
        return view('admin.movetoserver', compact('bkuplink', 'chk'));
    }


    public function clear_bkup()
    {
        $destination  = public_path() . '/install';
        $bkuplink = "";
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            unlink(public_path($chk));
        }

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        $handle = fopen('backup.txt', 'w+');
        fwrite($handle, "");
        fclose($handle);

        return redirect()->back()->with('success', 'Backup file Deleted Successfully!');
    }


    public function activation()
    {
        $activation_data = "";
        if (file_exists(public_path() . '/project/license.txt')) {
            $license = file_get_contents(public_path() . '/project/license.txt');
            if ($license != "") {
                $activation_data = "<i style='color:darkgreen;' class='icofont-check-circled icofont-4x'></i><br><h3 style='color:darkgreen;'>Your System is Activated!</h3><br> Your License Key:  <b>" . $license . "</b>";
            }
        }
        return view('admin.activation', compact('activation_data'));
    }


    public function activation_submit(Request $request)
    {

        $purchase_code =  $request->pcode;
        $my_script =  'Genius Bank - All in One Digital Banking System';
        $my_domain = url('/');

        $varUrl = str_replace(' ', '%20', config('services.genius.ocean') . 'purchase112662activate.php?code=' . $purchase_code . '&domain=' . $my_domain . '&script=' . $my_script);

        if (ini_get('allow_url_fopen')) {
            $contents = file_get_contents($varUrl);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $varUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $contents = curl_exec($ch);
            curl_close($ch);
        }

        $chk = json_decode($contents, true);

        if ($chk['status'] != "success") {

            $msg = $chk['message'];
            return response()->json($msg);
        } else {
            $this->setUp($chk['p2'], $chk['lData']);

            if (file_exists(public_path() . '/rooted.txt')) {
                unlink(public_path() . '/rooted.txt');
            }

            $fpbt = fopen(public_path() . '/project/license.txt', 'w');
            fwrite($fpbt, $purchase_code);
            fclose($fpbt);

            $msg = 'Congratulation!! Your System is successfully Activated.';
            return response()->json($msg);
        }
    }

    function setUp($mtFile, $goFileData)
    {
        $fpa = fopen(public_path() . $mtFile, 'w');
        fwrite($fpa, $goFileData);
        fclose($fpa);
    }



    public function movescript()
    {
        ini_set('max_execution_time', 3000);

        $destination  = public_path() . '/install';
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            unlink(public_path($chk));
        }

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        $src = base_path() . '/vendor/update';
        $this->recurse_copy($src, $destination);
        $files = public_path();
        $bkupname = 'GeniusCart-By-GeniusOcean-' . date('Y-m-d') . '.zip';
        $zip = Zip::create($bkupname)->add($files, true);
        $zip->close();

        $handle = fopen('backup.txt', 'w+');
        fwrite($handle, $bkupname);
        fclose($handle);

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        return response()->json(['status' => 'success', 'backupfile' => url($bkupname), 'filename' => $bkupname], 200);
    }

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function moduleupdate(Request $request)
    {
        $input = $request->all();

        $data = tenancy()->central(function ($tenant) {
            return Admin::findOrFail($tenant->id);
        });

        if (!empty($request->section)) {
            $input['section'] = implode(" , ", $request->section);
        } else {
            $input['section'] = '';
        }
        $data->section = $input['section'];
        $data->update();
        $msg = 'Data Updated Successfully.';

        return response()->json($msg);
    }

    public function profileupdatecontact(Request $request)
    {
        //--- Validation Section

        $rules = [
            'fullname'   => 'required',
            'contact'   => 'required',
            'your_email'   => 'required',
            'your_phone'   => 'required',
            'your_address'   => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();
        if ($request->input('contact_id') > 0) {
            $id = $request->input('contact_id');
            $contact = tenancy()->central(function ($tenant) use ($id) {
                return Contact::findOrFail($id);
            });

            $contact->full_name    =  $request->input('fullname');
            $contact->contact     =  $request->input('contact');

            // $contact->dob           = $request->input('dob') ? date('Y-m-d', strtotime($request->input('dob'))) : '';
            $contact->personal_code = $request->input('personal_code');
            $contact->c_email       = $request->input('your_email');
            $contact->c_phone       = $request->input('your_phone');
            $contact->c_address     = $request->input('your_address');
            $contact->c_city        = $request->input('c_city');
            $contact->c_zip_code    = $request->input('c_zipcode');
            $contact->c_country     = $request->input('c_country_id');
            $contact->id_number     = $request->input('your_id');
            $contact->issued_authority = $request->input('issued_authority');
            // $contact->date_of_issue         = $request->input('issue_date') != "" ? date('Y-m-d', strtotime($request->input('issue_date'))) : '';
            // $contact->date_of_expire        = $request->input('expire_date') != "" ? date('Y-m-d', strtotime($request->input('expire_date'))) : '';

            if ($contact->save()) {
                $msg = 'Successfully updated your contact information.';
                return response()->json($msg);
            } else {
                $msg = 'Successfully updated your contact information.';
                return response()->json($msg);
            }
        } else {
            $contact = tenancy()->central(function ($tenant) use ($request) {
                $contact = new Contact();

                $contact->full_name     =  $request->input('fullname');
                $contact->contact       =  $request->input('contact');
                $contact->user_id       = $tenant->id;
                $contact->dob           = $request->input('dob')  != "" ? date('Y-m-d', strtotime($request->input('dob'))) : '';
                $contact->personal_code = $request->input('personal_code');
                $contact->c_email       = $request->input('your_email');
                $contact->c_phone       = $request->input('your_phone');
                $contact->c_address     = $request->input('your_address');
                $contact->c_city        = $request->input('c_city');
                $contact->c_zip_code    = $request->input('c_zipcode');
                $contact->c_country     = $request->input('c_country_id');
                $contact->id_number             = $request->input('your_id');
                $contact->issued_authority      = $request->input('issued_authority');
                $contact->date_of_issue         = $request->input('date_of_issue') != "" ? date('Y-m-d', strtotime($request->input('date_of_issue'))) : '';
                $contact->date_of_expire        = $request->input('date_of_expire') != "" ? date('Y-m-d', strtotime($request->input('date_of_expire'))) : '';
                $contact->save();
                return $contact;
            });
            $msg = 'Successfully updated your contact information.';
            return response()->json($msg);
        }
    }
}
