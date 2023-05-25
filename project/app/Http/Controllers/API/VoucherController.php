<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Currency;
use App\Models\Charge;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;

class VoucherController extends Controller
{
    public $successStatus = 200;
    /*********************START VOUCHER API****************************/

    public function vouchers()
    {
        try{
            $user_id = Auth::user()->id;
            $data['vouchers'] = Voucher::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(20);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create()
    {
        try {
            $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->where('wallet_type',1)->get();
            $data['recentVouchers'] = Voucher::where('user_id',auth()->id())->latest()->take(7)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function submit(Request $request)
    {
        try {
            $rules = [
                'wallet_id' => 'required|integer',
                'amount' => 'required|numeric|gt:0'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $wallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',auth()->id())->first();
            if(!$wallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Wallet not found']);

            $rate = getRate($wallet->currency);

            $user= auth()->user();
            $global_cost = 0;
            $transaction_global_cost = 0;
            $custom_cost = 0;
            $transaction_custom_cost = 0;

            $finalCharge = $custom_cost+$global_cost+$transaction_global_cost+$transaction_custom_cost;
            $finalAmount = $request->amount + $finalCharge;

            $userBalance = user_wallet_balance(auth()->id(), $wallet->currency_id);

            if($finalAmount > $userBalance) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Wallet has insufficient balance']);

            $voucher = new Voucher();
            $voucher->user_id = auth()->id();
            $voucher->currency_id = $wallet->currency_id;
            $voucher->amount = $request->amount;
            $voucher->code = randNum(10).'-'.randNum(10);
            $voucher->save();

            user_wallet_decrement(auth()->id(),$wallet->currency_id,$finalAmount);

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $wallet->currency->id;
            $trnx->amount      = $finalAmount;

            $trans_wallet = get_wallet(auth()->id(),$wallet->currency_id);
            $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trnx->charge      = $finalCharge;
            $trnx->remark      = 'create_voucher';
            $trnx->type        = '-';
            $trnx->details     = trans('Voucher created');
            $trnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"Vocher System"}';
            $trnx->save();

            $currency = Currency::findOrFail($wallet->currency_id);
            mailSend('voucher_create',[ 'date_time'=>$trnx->created_at, 'trnx' => $trnx->trnx, 'amount' => $trnx->amount, 'curr' => $currency->code], auth()->user());


            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }


    }

    public function reedemForm()
    {
        try {
            $data['recentReedemed'] = Voucher::where('status',1)->where('reedemed_by',auth()->id())->take(7)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function reedemSubmit(Request $request)
    {
        try {
            $rules = [
                'code' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $voucher = Voucher::where('code',$request->code)->where('status',0)->first();

            if(!$voucher){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid voucher code']);
            }

            if( $voucher->user_id == auth()->id()){
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Can\'t reedem your own voucher']);
            }

            $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',auth()->id())->where('wallet_type', 1)->first();
            if(!$wallet){
               $gs = Generalsetting::first();
               $wallet = Wallet::create([
                   'user_id' => auth()->id(),
                   'user_type' => 1,
                   'currency_id' => $voucher->currency_id,
                   'balance'   => 0,
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
               $trans->amount      = 0;
               $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
               $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
               $trans->charge      = $chargefee->data->fixed_charge;
               $trans->type        = '-';
               $trans->remark      = 'account-open';
               $trans->details     = trans('Wallet Create');
               $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
               $trans->save();

               $def_currency = Currency::findOrFail(defaultCurr());
               $currency = Currency::findOrFail($voucher->currency_id);
               mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'def_curr' => $def_currency->code, 'type'=>'Current', 'date_time'=> dateFormat($trans->created_at)], $user);
               send_notification($user->id, 'New Current Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

               user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
               user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            $wallet->balance += $voucher->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $wallet->currency->id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $voucher->amount;
            $trnx->charge      = 0;
            $trnx->type        = '+';
            $trnx->remark      = 'reedem_voucher';
            $trnx->details     = trans('Voucher reedemed');
            $trnx->data        = '{"sender":"Vocher System", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $trnx->save();

            $voucher->status = 1;
            $voucher->reedemed_by = auth()->id();
            $voucher->update();

            mailSend('voucher_reedem',[ 'date_time'=>$trnx->created_at, 'trnx' => $trnx->trnx, 'amount' => $trnx->amount, 'curr' => $wallet->currency->code], auth()->user());


            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher reedemed successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }


    }



    public function reedemHistory()
    {
        try{
            $user_id = Auth::user()->id;
            $data['vouchers'] = Voucher::with('currency')->where('status',1)->where('reedemed_by',$user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

/*********************END VOUCHER API******************************/
}
