<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
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
        return view('user.ownaccounttransfer.index',compact('wallets','currencies'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|gt:0',
            'from_wallet_id' => 'required|integer'
        ],[
            'from_wallet_id.required' => 'From currency is required',
        ]);

        $charge  = charge('money-exchange');

        $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',auth()->id())->where('user_type',1)->firstOrFail();

        $toWallet = Wallet::where('currency_id',$fromWallet->currency_id)->where('user_id',auth()->id())->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();

        if(!$toWallet){
            $gs = Generalsetting::first();
            $toWallet = Wallet::create([
                'user_id'     => auth()->id(),
                'user_type'   => 1,
                'currency_id' => $fromWallet->currency_id,
                'balance'     => 0,
                'wallet_type' => $request->wallet_type,
                'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
            ]);
        }
        $user= auth()->user();
        $global_charge = Charge::where('name', 'Exchange Money')->where('plan_id', $user->bank_plan_id)->first();
        $global_cost = 0;
        $transaction_global_cost = 0;
        $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;
        if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
            return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum );
        }
        $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $custom_cost = 0;
        $transaction_custom_cost = 0;
        if(check_user_type(3))
        {
            $custom_charge = Charge::where('name', 'Exchange Money')->where('user_id', $user->id)->first();
            if($custom_charge)
            {
                $custom_cost = $custom_charge->data->fixed_charge + ($request->amount/100) * $custom_charge->data->percent_charge;
            }
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user);
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }



        $defaultAmount = $request->amount / $fromWallet->currency->rate;
        $finalAmount   = amount($defaultAmount * $toWallet->currency->rate,$toWallet->currency->type);

        $charge = amount($custom_cost+$global_cost+$transaction_global_cost+$transaction_custom_cost,$fromWallet->currency->type);
        $totalAmount = amount(($request->amount +  $charge),$fromWallet->currency->type);

        if($fromWallet->balance < $totalAmount){
            return back()->with('error','Insufficient balance to your '.$fromWallet->currency->code.' wallet');
        }

        $fromWallet->balance -=  $totalAmount;
        $fromWallet->update();
        if (check_user_type(3)) {
            user_wallet_increment($user->id, $fromWallet->currency_id, $custom_cost+$transaction_custom_cost, 6);
        }

        $toWallet->balance += $finalAmount;
        $toWallet->update();


        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $fromWallet->currency->id;
        $trnx->amount      = $request->amount + $charge;
        $trnx->charge      = $charge;
        $trnx->remark      = 'Own_transfer';
        $trnx->type        = '-';
        $trnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $trnx->save();

        $toTrnx              = new Transaction();
        $toTrnx->trnx        = $trnx->trnx;
        $toTrnx->user_id     = auth()->id();
        $toTrnx->user_type   = 1;
        $toTrnx->currency_id = $toWallet->currency->id;
        $toTrnx->amount      = $finalAmount;
        $toTrnx->charge      = 0;
        $toTrnx->remark      = 'Own_transfer';
        $toTrnx->type          = '+';
        $toTrnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $toTrnx->save();

        @mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($trnx->created_at)],auth()->user());

        return back()->with('success','Money exchanged successfully.');
    }
}
