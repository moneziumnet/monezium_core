<?php

namespace App\Http\Controllers\Staff;

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
        $this->middleware('auth:staff');
    }


    public function index()
    {
        $gs = Generalsetting::first();
        $def_currency = Currency::findOrFail(defaultCurr());
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$def_currency->code);
        $rate = json_decode($response->getBody());

        $data['blogs'] = Blog::all();
        $data['deposits'] = Deposit::all();
        $deposits = DepositBank::where('status', 'complete')->get();
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

        $withdraws = BalanceTransfer::where('status', 1)->where('type', 'other')->get();
        $withdraw_balance = 0;
        foreach ($withdraws as $value) {
            $currency = Currency::findOrFail($value->currency_id)->code;
            $withdraw_balance = $withdraw_balance + $value->amount / $rate->data->rates->$currency;
            $charge_balance = $charge_balance + $value->cost / $rate->data->rates->$currency;
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

        $deposits = DepositBank::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus('complete')
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->updated_at)->format('Y-m'); // grouping by months
        });
        $withdraws = BalanceTransfer::select('id', 'updated_at', 'amount', 'currency_id' )->whereStatus(1)->where('type', 'other')
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


        // if (file_exists(public_path().'/rooted.txt')){
        //     $rooted = file_get_contents(public_path().'/rooted.txt');
        //     if ($rooted < date('Y-m-d', strtotime("+10 days"))){
        //         $activation_notify = "<i class='icofont-warning-alt icofont-4x'></i><br>Please activate your system.<br> If you do not activate your system now, it will be inactive on ".$rooted."!!<br><a href='".url('/admin/activation')."' class='btn btn-success'>Activate Now</a>";
        //     }
        // }





        

        return view('staff.dashboard', $data);
    }
    
}
