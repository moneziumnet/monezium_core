<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\ExchangeMoney;
use App\Models\Charge;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Auth;
use Datatables;

class OwnTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->get();
        $currencies = Currency::where('status',1)->get();
        $user = auth()->user();
        return view('user.ownaccounttransfer.index',compact('wallets','currencies', 'user'));
    }

    public function transfer(Request $request)
    {
        $user = auth()->user();
        if($user->paymentcheck('Payment between accounts')) {
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }

        if(!isset($request->from_wallet_id)) {
            return back()->with('error','Please select Currency');
        }
        if(!isset($request->amount)) {
            return back()->with('error','Please input amount');
        }
        if(!isset($request->wallet_type)) {
            return back()->with('error','Please select Wallet');
        }

        $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',auth()->id())->where('user_type',1)->firstOrFail();

        $toWallet = Wallet::where('currency_id',$fromWallet->currency_id)->where('user_id',auth()->id())->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();
        $currency =  Currency::findOrFail($fromWallet->currency_id);
        if ($currency->type == 2) {
            $address = RPC_ETH('personal_newAccount',['123123']);
            if ($address == 'error') {
                return back()->with('error','You can not create this wallet because there is some issue in crypto node.');
            }
            $keyword = '123123';
        }
        else {
            $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
            $keyword = '';
        }
        if(!$toWallet){
            $gs = Generalsetting::first();
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
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;

                $trans_wallet = get_wallet($user->id, 1, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_issuance';
                $trans->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"System Account"}';
                $trans->details     = trans('Card Issuance');
                $trans->save();
            }
            else {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;

                $trans_wallet = get_wallet($user->id, 1, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"System Account"}';
                $trans->save();
            }
            user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
        }

        if($fromWallet->balance < $request->amount){
            return back()->with('error','Insufficient balance to your '.$fromWallet->currency->code.' wallet');
        }

        $fromWallet->balance -=  $request->amount;
        $fromWallet->update();

        $toWallet->balance += $request->amount;
        $toWallet->update();


        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $fromWallet->currency->id;
        $trnx->wallet_id   = $fromWallet->id;
        $trnx->amount      = $request->amount ;
        $trnx->charge      = 0;
        $trnx->remark      = 'Own_transfer';
        $trnx->type        = '-';
        $trnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $trnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.auth()->user()->name.'"}';
        $trnx->save();

        $toTrnx              = new Transaction();
        $toTrnx->trnx        = $trnx->trnx;
        $toTrnx->user_id     = auth()->id();
        $toTrnx->user_type   = 1;
        $toTrnx->currency_id = $toWallet->currency->id;
        $toTrnx->wallet_id   = $toWallet->id;
        $toTrnx->amount      = $request->amount;
        $toTrnx->charge      = 0;
        $toTrnx->remark      = 'Own_transfer';
        $toTrnx->type          = '+';
        $toTrnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $toTrnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.auth()->user()->name.'"}';
        $toTrnx->save();

        @mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($trnx->created_at)],auth()->user());

        return back()->with('message','Money exchanged successfully.');
    }
}
