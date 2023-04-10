<?php

namespace App\Http\Controllers\API;

use App\Models\Wallet;
use App\Models\User;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\Generalsetting;
use App\Classes\GoogleAuthenticator;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;

class OwnTransferController extends Controller
{

    public function index()
    {
        try {
            $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->get();
            $currencies = Currency::where('status',1)->where('type', 1)->get();
            $user = auth()->user();
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'success', 'data' => compact('wallets','currencies', 'user')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function transfer(Request $request)
    {
        try {
            $user = auth()->user();
            if($user->paymentcheck('Payment between accounts')) {
                if ($user->payment_fa != 'two_fa_google') {
                    if ($user->two_fa_code != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
                else{
                    $googleAuth = new GoogleAuthenticator();
                    $secret = $user->go;
                    $oneCode = $googleAuth->getCode($secret);
                    if ($oneCode != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
            }

            if(!isset($request->from_wallet_id)) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please select Currency']);
            }
            if(!isset($request->amount)) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input amount']);
            }
            if(!isset($request->wallet_type)) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please select Wallet']);
            }

            $gs = Generalsetting::first();
            $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',auth()->id())->where('user_type',1)->firstOrFail();

            $toWallet = Wallet::where('currency_id',$fromWallet->currency_id)->where('user_id',auth()->id())->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();
            $currency =  Currency::findOrFail($fromWallet->currency_id);
            $rate = getRate($currency);
            if(!$toWallet){
                $toWallet = Wallet::create([
                    'user_id'     => auth()->id(),
                    'user_type'   => 1,
                    'currency_id' => $fromWallet->currency_id,
                    'balance'     => 0,
                    'wallet_type' => $request->wallet_type,
                    'wallet_no' => $address,
                    'keyword' => $keyword
                ]);
                if($request->wallet_type == 2) {
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee){
                        $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount      = 0;

                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'card-issuance';
                    $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->details     = trans('Card Issuance');
                    $trans->save();
                }
                else {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();

                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $trans->amount      = 0;
                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'account-open';
                    $trans->details     = trans('Wallet Create');
                    $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();
                }
                $currency = Currency::findOrFail(defaultCurr());
                $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');

                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>$wallet_type_list[$request->wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                send_notification($user->id, 'New '.$wallet_type_list[$request->wallet_type].' Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }


            if($fromWallet->balance < $request->amount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your '.$fromWallet->currency->code.' wallet']);
            }

            $transaction_global_cost = 0;
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'payment_between_accounts');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
                user_wallet_increment(0, $fromWallet->currency->id, $transaction_global_cost*$rate, 9);
            }
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user,  'payment_between_accounts');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'payment_between_accounts_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost*$rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'payment_between_accounts_manager_fee';
                    user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost*$rate, 10);
                    $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 10);
                }
                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->currency_id = $fromWallet->currency->id;
                $trans->amount      = $transaction_custom_cost*$rate;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Own Money Transfer');
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'"}';
                $trans->save();
            }


            $fromWallet->balance -=  $request->amount;
            $fromWallet->update();

            $toWallet->balance += $request->amount-($transaction_global_cost +  $transaction_custom_cost)*$rate;
            $toWallet->update();


            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $fromWallet->currency->id;
            $trnx->wallet_id   = $fromWallet->id;
            $trnx->amount      = $request->amount;
            $trnx->charge      = ($transaction_global_cost + $transaction_custom_cost)*$rate;
            $trnx->remark      = 'payment_between_accounts';
            $trnx->type        = '-';
            $trnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'"}';
            $trnx->save();

            $toTrnx              = new Transaction();
            $toTrnx->trnx        = $trnx->trnx;
            $toTrnx->user_id     = auth()->id();
            $toTrnx->user_type   = 1;
            $toTrnx->currency_id = $toWallet->currency->id;
            $toTrnx->wallet_id   = $toWallet->id;
            $toTrnx->amount      = $request->amount-($transaction_global_cost +  $transaction_custom_cost)*$rate;
            $toTrnx->charge      = 0;
            $toTrnx->remark      = 'payment_between_accounts';
            $toTrnx->type          = '+';
            $toTrnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
            $toTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(auth()->user()->company_name ?? auth()->user()->name).'"}';
            $toTrnx->save();

            mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount(($transaction_global_cost +  $transaction_custom_cost)*$rate,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($request->amount-($transaction_global_cost +  $transaction_custom_cost)*$rate,$toWallet->currency->type,3),'date_time'=> dateFormat($trnx->created_at)],auth()->user());
            send_notification(auth()->id(), amount($request->amount, $fromWallet->currency->type, 3).$fromWallet->currency->code.' Money is exchanged to '.amount($request->amount-($transaction_global_cost +  $transaction_custom_cost)*$rate,$toWallet->currency->type,3).$toWallet->currency->code."\n Charge Fee : ".amount(($transaction_global_cost +  $transaction_custom_cost)*$rate,$fromWallet->currency->type,3).$fromWallet->currency->code, route('admin-user-transactions', auth()->id()));

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money exchanged successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
