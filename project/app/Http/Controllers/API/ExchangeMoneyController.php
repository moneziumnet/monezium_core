<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserApiCred;
use App\Models\ExchangeMoney;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\Generalsetting;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ExchangeMoneyController extends Controller
{
    public $successStatus = 200;
    /************Start Exchange API***************/
    public function exchangemoneyhistory(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $search = $request->transaction_no;
            $exchanges = ExchangeMoney::whereUserId($user_id)
                        ->when($search,function($q) use($search){
                                return $q->where('trnx',$search);
                            }
                        )->with(['fromCurr','toCurr'])->latest()->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $exchanges]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function exchangerecents(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $recentExchanges = ExchangeMoney::whereUserId($user_id)->with(['fromCurr','toCurr'])->orderBy('id','desc')->take(10)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $recentExchanges]);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function exchangemoney(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            // $request->validate([
            //     'amount'            => 'required|gt:0',
            //     'from_wallet_id'    => 'required|integer',
            //     'to_wallet_id'      => 'required|integer'
            // ],[
            //     'from_wallet_id.required' => 'From currency is required',
            //     'to_wallet_id.required' => 'To currency is required',
            // ]);

            $rules = ([
                'amount'            => 'required|gt:0',
                'from_wallet_id'    => 'required|integer',
                'to_wallet_id'      => 'required|integer'
            ]);
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $charge  = charge('money-exchange');

            $fromWallet = Wallet::where('id',$request->from_wallet_id)->where('user_id',$user_id)->where('user_type',1)->firstOrFail();

            $toWallet = Wallet::where('currency_id',$request->to_wallet_id)->where('user_id',$user_id)->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();

        if(!$toWallet){
            $gs = Generalsetting::first();
            $toWallet = Wallet::create([
                'user_id'     => $user_id,
                'user_type'   => 1,
                'currency_id' => $request->to_wallet_id,
                'balance'     => 0,
                'wallet_type' => $request->wallet_type,
                'wallet_no'     => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
            ]);

            $user = User::findOrFail($user_id);
            if ($request->wallet_type == 2) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee){
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user_id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user_id, 1, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_issuance';
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
                $trans->user_id     = $user_id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user_id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->save();
            }

            user_wallet_decrement($user_id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

        }


        $user= User::whereId($user_id)->first();
        $global_charge      = Charge::where('name', 'Exchange Money')->where('plan_id', $user->bank_plan_id)->first();
        $global_cost        = 0;
        $transaction_global_cost = 0;
        $global_cost = $global_charge->data->fixed_charge + ($request->amount/100) * $global_charge->data->percent_charge;



        if ($request->amount < $global_charge->data->minimum || $request->amount > $global_charge->data->maximum) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_charge->data->maximum.' and Min value is '.$global_charge->data->minimum]);
        }

        $transaction_global_fee = check_global_transaction_fee($request->amount, $user);
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $custom_cost = 0;
        $transaction_custom_cost = 0;

        $explode = explode(',',$user->user_type);

        if(in_array(4,$explode))
        {
            $transaction_custom_fee = check_custom_transaction_fee($request->amount, $user);
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }


        $defaultAmount = $request->amount / getRate($fromWallet->currency);
        $finalAmount   = $defaultAmount * getRate($toWallet->currency);

        $charge = $custom_cost+$global_cost+$transaction_global_cost+$transaction_custom_cost;
        $totalAmount = $request->amount +  $charge;

        if($fromWallet->balance < $totalAmount){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your '.$fromWallet->currency->code.' wallet']);
        }

        $fromWallet->balance -=  $totalAmount;
        $fromWallet->update();

        $toWallet->balance += $finalAmount;
        $toWallet->update();

        $exchange                   = new ExchangeMoney();
        $exchange->trnx             = str_rand();
        $exchange->from_currency    = $fromWallet->currency->id;
        $exchange->to_currency      = $toWallet->currency->id;
        $exchange->user_id          = $user_id;
        $exchange->charge           = $charge;
        $exchange->from_amount      = $request->amount;
        $exchange->to_amount        = $finalAmount;
        $exchange->save();
        //return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success']);

        //@mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($exchange->created_at)],$user_id);

        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money exchanged successfully.']);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    /************End Exchange API***************/
}
