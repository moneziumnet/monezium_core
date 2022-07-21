<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\ExchangeMoney;
use App\Models\Charge;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class ExchangeMoneyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function exchangeForm()
    {
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->get();
        $currencies = Currency::where('status',1)->get();
        $charge  = charge('money-exchange');
        $recentExchanges = ExchangeMoney::where('user_id',auth()->id())->with(['fromCurr','toCurr'])->latest()->take(7)->get();
        return view('user.exchange.exchange',compact('wallets','charge','currencies','recentExchanges'));
    }

    public function submitExchange(Request $request)
    {
        $request->validate([
            'amount' => 'required|gt:0',
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer'
        ],[
            'from_wallet_id.required' => 'From currency is required',
            'to_wallet_id.required' => 'To currency is required',
        ]);

        $charge  = charge('money-exchange');

        $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',auth()->id())->where('user_type',1)->firstOrFail();

        $toWallet = Wallet::where('currency_id',$request->to_wallet_id)->where('user_id',auth()->id())->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();

        if(!$toWallet){
            $gs = Generalsetting::first();
            $toWallet = Wallet::create([
                'user_id'     => auth()->id(),
                'user_type'   => 1,
                'currency_id' => $request->to_wallet_id,
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
                if ($request->amount < $custom_charge->data->minimum || $request->amount > $custom_charge->data->maximum) {
                    return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$custom_charge->data->maximum.' and Min value is '.$custom_charge->data->minimum );
                }
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

        $toWallet->balance += $finalAmount;
        $toWallet->update();

        $exchange = new ExchangeMoney();
        $exchange->trnx = str_rand();
        $exchange->from_currency = $fromWallet->currency->id;
        $exchange->to_currency = $toWallet->currency->id;
        $exchange->user_id = auth()->id();
        $exchange->charge = $charge;
        $exchange->from_amount = $request->amount;
        $exchange->to_amount = $finalAmount;
        $exchange->save();

        @mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($exchange->created_at)],auth()->user());

        return back()->with('success','Money exchanged successfully.');
    }

    public function exchangeHistory()
    {
        $search = request('search');
        $exchanges = ExchangeMoney::where('user_id',auth()->id())->when($search,function($q) use($search){return $q->where('trnx',$search);})->with(['fromCurr','toCurr'])->latest()->paginate(15);
        return view('user.exchange.history',compact('exchanges','search'));
    }
}
