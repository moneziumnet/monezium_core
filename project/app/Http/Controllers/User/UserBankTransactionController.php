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

class UserBankTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $user = Auth::user();
        $data['bankaccounts'] = BankAccount::where('user_id', $user->id)->orderBy('id', 'asc')->get();
        if(count($data['bankaccounts']) > 0) {
            $bankaccount = $data['bankaccounts'][0];
            $bankdeposits = DepositBank::where('sub_bank_id', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 'complete')->pluck('deposit_number');
            $balancetransfer = BalanceTransfer::where('subbank', $bankaccount->subbank_id)->where('currency_id', $bankaccount->currency_id)->where('user_id', auth()->id())->where('status', 1)->where('type', 'other')->pluck('transaction_no');

            $data['transactions'] = Transaction::where('user_id',auth()->id())->whereIn('remark', ['External_Payment', 'Deposit_create' ])->whereIn('trnx', $bankdeposits)->orwhereIn('trnx', $balanetransfer)->latest()->paginate(20);
        }
        else

        return view('user.bank.index',$data);
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

}
