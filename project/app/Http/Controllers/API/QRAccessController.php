<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\CryptoDeposit;
use App\Models\Currency;
use App\Models\DepositBank;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class QRAccessController extends Controller
{
    public function index(Request $request) {
        $site_key = $request->site_key ?? Session::get('site_key');
        $currencylist = Currency::whereStatus(1)->get();
        if (!isEnabledUserModule('Crypto'))
            $currencylist = Currency::whereStatus(1)->where('type', 1)->get();

        $user_api = UserApiCred::where('access_key', $site_key)->first();
        if($user_api) {
            Session::put('site_key', $site_key);
            $user = $user_api->user;
            $bankaccounts = BankAccount::where('user_id', $user->id)->get();
            $crypto_ids =  Wallet::where('user_id', $user->id)->where('user_type',1)->where('wallet_type', 8)->pluck('currency_id')->toArray();
            $cryptolist = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();
            return view('merchantqr.payment', compact('bankaccounts','cryptolist','user', 'currencylist'));
        } else {
            return view('merchantqr.error');
        }
    }

    public function crypto_pay(Request $request) {
        $pre_currency = Currency::findOrFail($request->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $data['total_amount'] = $request->amount;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
        $data['wallet'] =  Wallet::where('user_id', $request->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();

        if(!$data['wallet']) {
            return redirect()->back()->with('error', $select_currency->code .' Crypto wallet does not existed in sender.');
        }
        return view('merchantqr.crypto', $data);
    }

    public function pay_submit(Request $request) {
        if($request->payment == 'gateway'){
            return response()->json([
                'type' => 'mt_payment_success',
                'payload' => 'Gateway Payment completed'
            ]);
        } else if($request->payment == 'bank_pay'){
            $bankaccount = BankAccount::where('id', $request->bank_account)->first();

            $deposit = new DepositBank();
            $deposit['deposit_number'] = $request->deposit_no;
            $deposit['user_id'] = $request->user_id;
            $deposit['currency_id'] = $request->currency_id;
            $deposit['amount'] = $request->amount;
            $deposit['sub_bank_id'] = $bankaccount->subbank_id;
            $deposit['status'] = "pending";
            $deposit->save();
            $user = User::findOrFail($request->user_id);
            $currency = Currency::findOrFail($request->currency_id);
            $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);

            mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
            send_notification($request->user_id, 'Bank has been deposited. Please check.', route('admin.deposits.bank.index'));
            $currency = Currency::findOrFail($request->currency_id);
            send_whatsapp($request->user_id, 'Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_telegram($request->user_id, 'Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_staff_telegram('Bank has been deposited '."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

            return redirect(route('user.dashboard'))->with('message', 'Bank Payment completed');
        } else if($request->payment == 'crypto'){
            $data = new CryptoDeposit();
            $data->currency_id = $request->currency_id;
            $data->amount = $request->amount;
            $data->user_id = $request->user_id;
            $data->address = $request->address;
            // $data->proof = '';
            $data->save();
            return redirect(route('user.dashboard'))->with('message', 'Crypto Payment completed');
        } else if($request->payment == 'wallet'){
            if(Auth::guest()) {
                session()->put('setredirectroute', route('qr.pay.index'));
                return redirect(route('user.login'))->with('error', 'You need to login MT Payment System.');
            }
            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 1)->first();

            if(!$wallet){
                $gs = Generalsetting::first();
                $wallet =  Wallet::create([
                    'user_id'     => auth()->id(),
                    'user_type'   => 1,
                    'currency_id' => $request->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail(auth()->id());

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }
            if($wallet->balance < $request->amount) {
                return redirect(route('user.dashboard'))->with('error', 'Insufficient balance to your wallet');
            }

            $wallet->balance -= $request->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $request->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $request->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'merchant_qr_payment';
            $trnx->type        = '-';
            $trnx->details     = trans('Payment to merchant');
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'"}';
            $trnx->save();

            $rcvWallet = Wallet::where('user_id',$request->user_id)->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 1)->first();

            if(!$rcvWallet){
                $gs = Generalsetting::first();
                $rcvWallet =  Wallet::create([
                    'user_id'     => $request->user_id,
                    'user_type'   => 1,
                    'currency_id' => $request->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail($request->user_id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $request->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans_wallet = get_wallet($request->user_id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();

                user_wallet_decrement($request->user_id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            $rcvWallet->balance += $request->amount;
            $rcvWallet->update();

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $request->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $request->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $request->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'merchant_qr_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Merchant Payment');
            $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($request->user_id)->company_name ?? User::findOrFail($request->user_id)->name).'"}';
            $rcvTrnx->save();

            return redirect(route('user.dashboard'))->with('message', 'Wallet Payment completed');
        }
    }
}
