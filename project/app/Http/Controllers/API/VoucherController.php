<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserApiCred;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class VoucherController extends Controller
{
    public $successStatus = 200;
    /*********************START VOUCHER API****************************/

    public function vouchers(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $data['vouchers']          = Voucher::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function createvoucher(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $request->validate([
                'wallet_id' => 'required|integer',
                'amount' => 'required|numeric|gt:0'
            ]);


        $wallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',$user_id)->first();
        if(!$wallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Wallet not found']);

        $charge = charge('create-voucher');
        $rate = getRate($wallet->currency);

        if($request->amount <  $charge->minimum * $rate || $request->amount >  $charge->maximum * $rate){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please follow the limit']);
        }

        $finalCharge = chargeCalc($charge,$request->amount,$rate);
        $finalAmount = numFormat($request->amount + $finalCharge);

        $commission  = ($request->amount * $charge->commission)/100;

        $voucher = new Voucher();
        $voucher->user_id = $user_id;
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $request->amount;
        $voucher->code = randNum(10).'-'.randNum(10);
        $voucher->save();
        $userBalance = user_wallet_balance($user_id, $wallet->currency_id);
        if($finalAmount > $userBalance) return response()->json(['status' => '401', 'error_code' => $finalAmount, 'message' => 'Wallet has insufficient balance']);

        user_wallet_decrement($user_id,$wallet->currency_id,$finalAmount);
        // $wallet->balance -=  $finalAmount;
        // $wallet->save();

        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = $user_id;
        $trnx->user_type   = 1;
        $trnx->currency_id = $wallet->currency->id;
        $trans_wallet = get_wallet($user_id,$wallet->currency_id);
        $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trnx->amount      = $finalAmount;
        $trnx->charge      = $finalCharge;
        $trnx->remark      = 'create_voucher';
        $trnx->type        = '-';
        $trnx->details     = trans('Voucher created');
        $trnx->save();

        user_wallet_increment($user_id,$wallet->currency_id,$commission);
        // $wallet->balance +=  $commission;
        // $wallet->save();

        $commissionTrnx              = new Transaction();
        $commissionTrnx->trnx        = $trnx->trnx;
        $commissionTrnx->user_id     = $user_id;
        $commissionTrnx->user_type   = 1;
        $commissionTrnx->currency_id = $wallet->currency->id;
        $commissionTrnx->amount      = $commission;
        $trans_wallet = get_wallet($user_id,$wallet->currency_id);
        $commissionTrnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $commissionTrnx->charge      = 0;
        $commissionTrnx->remark      = 'voucher_commission';
        $commissionTrnx->type        = '+';
        $commissionTrnx->details     = trans('Voucher commission');
        $commissionTrnx->save();
        $data['vouchers']          = Voucher::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher has been created successfully', 'data' => $data]);

        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function reedemvoucher(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $request->validate([
                'code'          => 'required',

            ],
            [
                'code.required' => 'Voucher is required'
            ]);

            $user = User::whereId($user_id)->first();

            $voucher = Voucher::where('code',$request->code)->where('status',0)->first();

            if(!$voucher){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid voucher code']);
            }

            if( $voucher->user_id == $user_id){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Can\'t reedem your own voucher']);
            }

            $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',$user_id)->first();
            if(!$wallet){
                $gs = Generalsetting::first();
                $wallet = Wallet::create([
                    'user_id' => $user_id,
                    'user_type' => 1,
                    'currency_id' => $voucher->currency_id,
                    'balance'   => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail($user_id);

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

                user_wallet_decrement($user_id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            $wallet->balance += $voucher->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $wallet->currency->id;
            $trnx->amount      = $voucher->amount;
            $trnx->wallet_id   = $wallet->id;
            $trnx->charge      = 0;
            $trnx->type        = '+';
            $trnx->remark      = 'reedem_voucher';
            $trnx->details     = trans('Voucher reedemed');
            $trnx->save();

            $voucher->status = 1;
            $voucher->reedemed_by = $user_id;
            $voucher->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Voucher reedemed successfully']);


        } catch (\Throwable $th) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
           }
    }

    public function reedemedhistory(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $data['vouchers'] = Voucher::with('currency')->where('status',1)->where('reedemed_by',$user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

/*********************END VOUCHER API******************************/
}
