<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Escrow;
use App\Models\Wallet;
use App\Models\Dispute;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\PlanDetail;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Helpers\MediaHelper;
use Illuminate\Http\Request;
use DB;



use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class EscrowController extends Controller
{
    public $successStatus = 200;
    /*********************START ESCROW API******************************/

    public function index()
    {
        try {
            $data['escrows'] = Escrow::where('user_id',auth()->id())->latest()->paginate(15);
            $data['wallets'] = Wallet::where('user_id',auth()->id())->where('wallet_type',5)->with('currency')->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function pending()
    {
        try {
            $data['escrows'] = Escrow::where('recipient_id',auth()->id())->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create()
    {
        try {
            $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->where('wallet_type',5)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'receiver'  => 'required|email',
                'wallet_id' => 'required|integer',
                'amount'    => 'required|numeric|gt:0',
                'description'    => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $gs = Generalsetting::first();

            $receiver = User::where('email',$request->receiver)->first();
            if(!$receiver) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Recipient not found']);

            $senderWallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',auth()->id())->first();

            if(!$senderWallet) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your wallet not found']);

            $currency = Currency::findOrFail($senderWallet->currency->id);
            $rate = getRate($currency);
            $user= auth()->user();
            $transaction_global_cost = 0;
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'escrow');
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'escrow')->first();
            if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ]);
            }
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'escrow');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                }
            }

            $finalCharge = $transaction_global_cost+$transaction_custom_cost;
            if($request->charge_pay) $finalAmount =  $request->amount + $finalCharge*$rate;
            else  $finalAmount =  $request->amount;

            if($senderWallet->balance < $finalAmount) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance.']);

            $senderWallet->balance -= $finalAmount;
            user_wallet_increment(0, $currency->id, $transaction_global_cost*$rate, 9);
            $senderWallet->update();
            if($user->referral_id != 0){
                $remark = 'Escrow_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $currency->id, $transaction_custom_cost*$rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $currency->id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'Escrow_manager_fee';
                    user_wallet_increment($user->referral_id, $currency->id, $transaction_custom_cost*$rate, 10);
                    $trans_wallet = get_wallet($user->referral_id, $currency->id, 10);
                }
                $supervisor_trnx = str_rand();

                $trans = new Transaction();
                $trans->trnx = $supervisor_trnx;
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency->id;
                $trans->amount      = $transaction_custom_cost*$rate;
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Make Escrow');
                $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "description": "'.$request->description.'"}';
                $trans->save();

            }

            $escrow               = new Escrow();
            $escrow->trnx         = str_rand();
            $escrow->user_id      = auth()->id();
            $escrow->recipient_id = $receiver->id;
            $escrow->description  = $request->description;
            $escrow->amount       = $request->amount;
            $escrow->pay_charge   = $request->charge_pay ? 1 : 0;
            $escrow->charge       = $finalCharge*$rate;
            $escrow->currency_id  = $currency->id;
            $escrow->save();

            $trnx              = new Transaction();
            $trnx->trnx        = $escrow->trnx;
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $currency->id;
            $trnx->wallet_id   = $senderWallet->id;
            $trnx->amount      = $finalAmount;
            $trnx->charge      = $finalCharge*$rate;
            $trnx->remark      = 'escrow';
            $trnx->type        = '-';
            $trnx->details     = trans('Made escrow to '). $receiver->email;
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$request->description.'"}';
            $trnx->save();
            send_notification(auth()->id(), 'New Escrow has been requested by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Escrow ID : ".$escrow->trnx, route('admin.escrow.onHold'));
            send_staff_telegram('New Escrow has been requested by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Escrow ID : ".$escrow->trnx."\n Please check.\n".route('admin.escrow.onHold'), 'Escrow');

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Escrow has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function calcharge($amount)
    {
        try {
            $user= auth()->user();
            $transaction_global_cost = 0;
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'escrow');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
            }
            $transaction_custom_cost = 0;
            if(check_user_type(4))
            {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'escrow');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
                }
            }

            $finalCharge = $transaction_global_cost+$transaction_custom_cost;
            $data['charge'] = $finalCharge;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function disputeForm($id)
    {
        try {
            $escrow = Escrow::where('id',$id)->first();
            if(!$escrow) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This escrow is not yours.']);
            }
            if (auth()->id() != $escrow->recipient_id && auth()->id() != $escrow->user_id){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid access']);
            }

            $messages = Dispute::where('escrow_id',$escrow->id)->with('user')->get();
            $data['escrow'] = $escrow;
            $data['messages'] = $messages;

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function disputeStore(Request $request,$escrow_id)
    {
        try {
            $rules = [
                'message'=>'required',
                'file' => 'mimes:png,jpeg,jpg|max:5186'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $escrow = Escrow::where('id',$escrow_id)->first();
            if(!$escrow) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This escrow is not yours.']);
            }
            if (auth()->id() != $escrow->recipient_id && auth()->id() != $escrow->user_id){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invalid access']);
            }
            if($escrow->status == 4) return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Dispute has been closed']);

            $escrow->status = 3;
            if($escrow->dispute_created == null) $escrow->dispute_created = auth()->id();
            $escrow->save();

            $dispute = new Dispute;
            $dispute->user_id = auth()->id();
            $dispute->escrow_id = $escrow_id;
            $dispute->message = $request->message;
            if($request->file) $dispute->file = MediaHelper::handleMakeImage($request->file);
            $dispute->save();

            $recipient = User::findOrFail($escrow->recipient_id);
            $owner = User::findOrFail($escrow->user_id);

            mailSend('escrow_dispute',[ 'date_time'=>$dispute->created_at, 'user_name' => (auth()->user()->company_name ?? auth()->user()->name), 'trnx' => $escrow->trnx, 'reason' => $dispute->message], $recipient);
            mailSend('escrow_dispute',[ 'date_time'=>$dispute->created_at, 'user_name' => (auth()->user()->company_name ?? auth()->user()->name), 'trnx' => $escrow->trnx, 'reason' => $dispute->message], $owner);


            send_notification($escrow->user_id, 'Dispute about Escrow has been created by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.'."\nEscrow ID:".$escrow->trnx, route('admin.escrow.disputed'));
            send_notification($escrow->recipient_id, 'Dispute about Escrow has been created by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.'."\nEscrow ID:".$escrow->trnx, route('admin.escrow.disputed'));

            send_staff_telegram('Dispute about Escrow has been created by '.(auth()->user()->company_name ?? auth()->user()->name)."\nEscrow ID:".$escrow->trnx."\n Please check.\n".route('admin.escrow.disputed'), 'Escrow');

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Replied submitted']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function release($id) {
        try {
            $escrow = Escrow::where('id',$id)->where('user_id',auth()->id())->whereIn('status',[0,3])->first();
            $recipient = User::findOrFail($escrow->recipient_id);
            $recipientWallet = Wallet::where('user_id',$recipient->id)
                                ->where('user_type',1)
                                ->where('currency_id',$escrow->currency_id)
                                ->where('wallet_type', 5)
                                ->first();

            if(!$recipientWallet){
                $gs = Generalsetting::first();
                $recipientWallet =  Wallet::create(
                    [
                        'user_id'      => $recipient->id,
                        'user_type'    => 1,
                        'currency_id'  => $escrow->currency_id,
                        'balance'      => 0,
                        'wallet_type' => 5,
                        'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                    ]
                );
                $user = User::findOrFail($recipient->id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $recipient->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = 0;
                $trans_wallet = get_wallet($recipient->id, defaultCurr());
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = $chargefee->data->fixed_charge;
                $trans->type        = '-';
                $trans->remark      = 'account-open';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.($recipient->company_name ?? $recipient->name).'", "receiver":"'.$gs->disqus.'", "description": "'.$escrow->description.'"}';
                $trans->save();

                $currency = Currency::findOrFail(defaultCurr());

                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Escrow', 'date_time'=> dateFormat($trans->created_at)], $user);
                send_notification($user->id, 'New Escrow Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

                user_wallet_decrement($recipient->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            $amount = $escrow->amount - $escrow->charge;

            $recipientWallet->balance += $amount;
            $recipientWallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = $escrow->trnx;
            $trnx->user_id     = $recipient->id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $escrow->currency_id;
            $trans->wallet_id   = $recipientWallet->id;
            $trnx->amount      = $amount;
            $trnx->charge      = $escrow->pay_charge == 0 ? $escrow->charge : 0;
            $trnx->remark      = 'escrow';
            $trnx->type        = '+';
            $trnx->details     = trans('Received escrow money '). $recipient->email;
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($recipient->company_name ?? $recipient->name).'", "description": "'.$escrow->description.'"}';
            $trnx->save();

            $escrow->status = 1;
            $escrow->save();

            $currency = Currency::findOrFail($escrow->currency_id);

            mailSend('escrow_release',[ 'amount' =>$amount, 'curr' => $currency->code, 'date_time'=>$trnx->created_at, 'user_name' => (auth()->user()->company_name ?? auth()->user()->name), 'trnx' => $escrow->trnx,'charge'=>  $trnx->charge], $recipient);

            send_notification($recipient->id, 'Holding Escrow has been released by '.(auth()->user()->company_name ?? auth()->user()->name)."\nEscrow ID:".$escrow->trnx, route('admin.escrow.manage'));
            send_staff_telegram('Holding Escrow has been released by '.(auth()->user()->company_name ?? auth()->user()->name)."\nEscrow ID:".$escrow->trnx."\n Please check.\n".route('admin.escrow.manage'), 'Escrow');

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Escrow has been released']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
/*********************END ESCROW API******************************/

}
