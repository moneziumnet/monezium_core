<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Escrow;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EscrowController extends Controller
{
    public $successStatus = 200;
    /*********************START ESCROW API******************************/
    public function myescrow(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['escrow']          = Escrow::with('currency','recipient')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function makeescrow(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $rules = [
                'receiver'          => 'required|email',
                'wallet_id'         => 'required|integer',
                'amount'            => 'required|numeric|gt:0',
                'description'       => 'required',
                'charge_pay'        => 'numeric'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $user = User::whereId($user_id)->first();

            $receiver = User::where('email',$request->receiver)->first();
            if(!$receiver) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Recipient not found']);

            $senderWallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->whereUserId($user_id)->first();

            if(!$senderWallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your wallet not found']);

            $currency = Currency::findOrFail($senderWallet->currency->id);
            $charge = charge('make-escrow');

            $finalCharge = chargeCalc($charge,$request->amount,getRate($currency));

            if($request->pay_charge) $finalAmount =  $request->amount + $finalCharge;
            else  $finalAmount =  $request->amount;

            if($senderWallet->balance < $finalAmount) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance']);

            $senderWallet->balance -= $finalAmount;
            $senderWallet->update();

            $escrow               = new Escrow();
            $escrow->trnx         = str_rand();
            $escrow->user_id      = $user_id;
            $escrow->recipient_id = $receiver->id;
            $escrow->description  = $request->description;
            $escrow->amount       = $request->amount;
            $escrow->pay_charge   = $request->pay_charge ? 1 : 0;
            $escrow->charge       = $finalCharge;
            $escrow->currency_id  = $currency->id;
            $escrow->save();

            $trnx              = new Transaction();
            $trnx->trnx        = $escrow->trnx;
            $trnx->user_id     = $user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $currency->id;
            $trnx->wallet_id   = $senderWallet->id;
            $trnx->amount      = $finalAmount;
            $trnx->charge      = $finalCharge;
            $trnx->remark      = 'make_escrow';
            $trnx->type        = '-';
            $trnx->details     = trans('Made escrow to '). $receiver->email;
            $trnx->data        = '{"description":"'.$request->description.'"}';
            $trnx->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Escrow has been created successfully', 'data' => $escrow]);

        } catch (\Throwable $th) {
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function escrowpending(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['escrow']          = Escrow::with('currency')->where('recipient_id',$user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/*********************END ESCROW API******************************/

}
