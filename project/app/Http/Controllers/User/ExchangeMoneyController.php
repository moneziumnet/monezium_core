<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\ExchangeMoney;
use App\Models\Charge;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\PlanDetail;
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
        $currencies = Currency::where('status',1)->whereType('1')->get();
        $crypto_currencies = Currency::where('status',1)->whereType('2')->get();
        $recentExchanges = ExchangeMoney::where('user_id',auth()->id())->with(['fromCurr','toCurr'])->latest()->take(7)->get();
        $user = auth()->user();
        return view('user.exchange.exchange',compact('wallets','currencies','recentExchanges', 'crypto_currencies', 'user'));
    }

    public function submitExchange(Request $request)
    {
        $user = auth()->user();
        if($user->payment_fa_yn == 'Y') {
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $request->validate([
            'amount' => 'required|gt:0',
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer'
        ],[
            'from_wallet_id.required' => 'From currency is required',
            'to_wallet_id.required' => 'To currency is required',
        ]);


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

            $user = User::findOrFail(auth()->id());
            if ($request->wallet_type == 2) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_issuance';
                $trans->details     = trans('Card Issuance');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();
            }
            else {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();
            }
            user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
        }
        $user= auth()->user();
        // $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();
        $transaction_global_cost = 0;
        // if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
        //     return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        // }
        // $transaction_global_fee = check_global_transaction_fee($request->amount, $user, 'send');
        // if($transaction_global_fee)
        // {
        //     $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        // }
        $transaction_custom_cost = 0;
        // if(check_user_type(3))
        // {
        //     $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user,  'send');
        //     if($transaction_custom_fee) {
        //         $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
        //     }
        // }



        $defaultAmount = $request->amount / $fromWallet->currency->rate;
        $finalAmount   = amount($defaultAmount * $toWallet->currency->rate,$toWallet->currency->type);

        $charge = amount($transaction_global_cost+$transaction_custom_cost,$fromWallet->currency->type);
        $totalAmount = amount(($request->amount +  $charge),$fromWallet->currency->type);

        if($fromWallet->balance < $totalAmount){
            return back()->with('error','Insufficient balance to your '.$fromWallet->currency->code.' wallet');
        }

        $fromWallet->balance -=  $totalAmount;
        $fromWallet->update();
        if (check_user_type(4)) {
            user_wallet_increment($user->id, $fromWallet->currency_id, $transaction_custom_cost, 6);
        }

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

        return back()->with('message','Money exchanged successfully.');
    }

    public function calcharge($amount)
    {
        $user= auth()->user();
        // $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();
        $transaction_global_cost = 0;
        // if ($amount < $global_range->min || $amount > $global_range->max) {
        //     return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        // }
        // $transaction_global_fee = check_global_transaction_fee($amount, $user, 'send');
        // if($transaction_global_fee)
        // {
        //     $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
        // }
        $transaction_custom_cost = 0;
        // if(check_user_type(3))
        // {
        //     $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'send');
        //     if($transaction_custom_fee) {
        //         $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
        //     }
        // }
        $finalCharge = $transaction_global_cost+$transaction_custom_cost;
        return $finalCharge;
    }

    public function exchangeHistory()
    {
        $search = request('search');
        $exchanges = ExchangeMoney::where('user_id',auth()->id())->when($search,function($q) use($search){return $q->where('trnx',$search);})->with(['fromCurr','toCurr'])->latest()->paginate(15);
        return view('user.exchange.history',compact('exchanges','search'));
    }
}
