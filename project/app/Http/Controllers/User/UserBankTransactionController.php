<?php

namespace App\Http\Controllers\User;

use PDF;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\DepositBank;
use App\Models\BalanceTransfer;
use App\Models\Wallet;
use App\Traits\Payout;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\WebhookRequest;
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

class UserBankTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $bankaccount = request('bankaccount');
        $user = Auth::user();
        $data['bankaccounts'] = BankAccount::where('user_id', $user->id)->orderBy('id', 'asc')->get();
        $data['transactions'] = [];
        if(count($data['bankaccounts']) > 0  && $bankaccount != null) {
            $bankaccount = BankAccount::where('user_id', $user->id)->where('iban', $bankaccount)->first();
            $bankdeposits = DepositBank::where('sub_bank_id', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 'complete')->pluck('deposit_number');
            $balancetransfer = BalanceTransfer::where('subbank', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 1)->where('type', 'other')->pluck('transaction_no');

            $data['transactions'] = Transaction::where('user_id',auth()->id())->whereIn('remark', ['External_Payment', 'Deposit_create' ])->whereIn('trnx', $bankdeposits)->orwhereIn('trnx', $balancetransfer)->latest()->paginate(20);
        }

        return view('user.bank.index',$data);
    }

    public function bank_transaction($id)
    {

        $user = Auth::user();
        $data['bankaccounts'] = BankAccount::where('user_id', $user->id)->orderBy('id', 'asc')->get();
        $bankaccount = BankAccount::where('id', $id)->first();
        $bankdeposits = DepositBank::where('sub_bank_id', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 'complete')->pluck('deposit_number');
        $balancetransfer = BalanceTransfer::where('subbank', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 1)->where('type', 'other')->pluck('transaction_no');

        $data['transactions'] = Transaction::where('user_id',auth()->id())->whereIn('remark', ['External_Payment', 'Deposit_create' ])->whereIn('trnx', $bankdeposits)->orwhereIn('trnx', $balancetransfer)->latest()->paginate(20);

        return view('user.bank.banktransaction',$data);
    }

    public function compare_transaction()
    {
        $balancetransfers = BalanceTransfer::whereUserId(auth()->id())->whereType('other')->orderBy('id','desc')->get();
        $deposits = DepositBank::orderby('id','desc')->whereUserId(auth()->id())->with('user')->get();
        $compare_list = [];
        foreach ($balancetransfers as $key => $value) {
            $temp = array();
            $temp['user_id'] = $value->user_id;
            $temp['type'] = 'External Transfer';
            $temp['trnx_no'] = $value->transaction_no;
            $temp['sender_name'] = auth()->user()->company_name ?? auth()->user()->name;
            $beneficiary = Beneficiary::findOrFail($value->beneficiary_id);
            $temp['receiver_name'] = $beneficiary->name;
            $currency = Currency::findOrFail($value->currency_id);
            $temp['amount'] = amount($value->final_amount, $currency->type, 2);
            $temp['currency_code'] = $currency->code;
            if ($value->status == 0 || $value->status == 3) {
                $status = 'pending';
            }
            else if($value->status == 1) {
                $status = 'complete';
            }
            else{
                $status = 'reject';
            }
            $temp['status'] = $status;
            $transaction = Transaction::where('user_id',auth()->id())->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->transaction_no.'%')->orWhere('trnx', $value->transaction_no)->first();

            $temp['tran_id'] = $transaction->id ?? null;
            $temp['date'] = $value->created_at;
            array_push($compare_list, $temp);

        }


        foreach ($deposits as $key => $value) {
            $temp = array();
            $temp['user_id'] = $value->user_id;
            $temp['type'] = 'Bank Deposit';
            $temp['trnx_no'] = $value->deposit_number;
            $send_info = WebhookRequest::where('transaction_id', 'LIKE', '%'.$value->deposit_number)->orWhere('reference', 'LIKE', '%'.$value->deposit_number)->with('currency')->first();
            $temp['sender_name'] = $send_info->sender_name ?? null;
            $temp['receiver_name'] = auth()->user()->company_name ?? auth()->user()->name;
            $currency = Currency::findOrFail($value->currency_id);
            $temp['amount'] = amount($value->amount, $currency->type, 2);
            $temp['currency_code'] = $currency->code;
            $temp['status'] = $value->status;
            $transaction = Transaction::where('user_id',auth()->id())->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->deposit_number.'%')->orWhere('trnx', $value->deposit_number)->first();

            $temp['tran_id'] = $transaction->id ?? null;
            $temp['date'] = $value->created_at;
            array_push($compare_list, $temp);

        }
        usort($compare_list, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $data['transactions'] = $compare_list;

        return view('user.bank.compare', $data);
    }

    public function summay_fee()
    {
        $user = Auth::user();
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $remark_list = Transaction::where('user_id',auth()->id())->pluck('remark');
        $remark_list = array_unique($remark_list->all());
        if($remark != 'all_mark' && $remark != null) {
            $transactions = Transaction::where('user_id',auth()->id())
            // ->where('wallet_id', $wallet_id)
            ->when($remark,function($q) use($remark){
                return $q->where('remark',$remark);
            })
            ->when($search,function($q) use($search){
                return $q->where('trnx','LIKE',"%{$search}%");
            })
            ->whereBetween('created_at', [$s_time, $e_time])
            ->with('currency')->latest()->paginate(20);
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id)->code;
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
            $rate = json_decode($response->getBody());
            $balance = 0;

            foreach($transactions as $key => $value) {
                    $code = $value->currency->code;
                    if($value->type == '+') {
                        $balance = $balance + $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
                    else {
                        $balance = $balance - $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
            }
            $balance = amount($balance, Currency::findOrFail($currency_id)->type, 2).$def_code;
            $flag = false;

        }
        else {
            $transactions = array();
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id)->code;
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
            $rate = json_decode($response->getBody());
            foreach ($remark_list as $key => $fee) {
                $fee_transactions = Transaction::where('user_id',auth()->id())
                // ->where('wallet_id', $wallet_id)
                ->when($fee,function($q) use($fee){
                    return $q->where('remark',$fee);
                })
                ->whereBetween('created_at', [$s_time, $e_time])
                ->with('currency')->latest()->paginate(20);
                $fee_balance = 0;
                foreach($fee_transactions as $key => $value) {
                    $code = $value->currency->code;
                    if($value->type == '+') {
                        $fee_balance = $fee_balance + $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
                    else {
                        $fee_balance = $fee_balance - $value->amount / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
                }
                $fee_balance = amount($fee_balance, Currency::findOrFail($currency_id)->type, 2).$def_code;
                array_push($transactions, array(
                    "fee"=> $fee,
                    "balance"=> $fee_balance
                ));

            }
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id);

            $balance = amount(0, $def_code->type, 2).$def_code->code;
            $flag = true;
        }

        return view('user.bank.summary',compact('user','transactions', 'search', 'remark_list', 's_time', 'e_time', 'balance' , 'flag'));
    }


}
