<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\BalanceTransfer;
use App\Models\MoneyRequest;
use App\Models\BankPlan;
use App\Models\SaveAccount;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SendController extends Controller
{
    public $successStatus = 200;

    ////////////////////////////////////Send Money//////////////////////////////////////////
    public function sendmoney(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $rules = [
                'account_number'    => 'required',
                'wallet_id'         => 'required',
                'account_name'      => 'required',
                'amount'            => 'required|numeric|min:0',
                'description'       => 'required',
                'code'              => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $user = User::whereId($user_id)->first();
            $ga = new GoogleAuthenticator();
            $secret = $user->go;
            $oneCode = $ga->getCode($secret);

            if ($oneCode != $request->code) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Two factor authentication code is wrong.']);
            }

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();

            $currency_id = $wallet->currency->id; //Currency::whereId($wallet_id)->first()->id;

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailySend = BalanceTransfer::whereUserId($user_id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

            if($dailySend > $bank_plan->daily_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily send limit over.']);
            }

            if($monthlySend > $bank_plan->monthly_send){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly send limit over.']);
            }

            $gs = Generalsetting::first();

            if($request->account_number == $user->account_number){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!!']);
            }

            if($request->amount < 0){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Request Amount should be greater than this!']);
            }

            if($request->amount > user_wallet_balance($user_id, $currency_id, $wallet->wallet_type)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance.']);
            }

            if($receiver = User::where('account_number',$request->account_number)->first()){
                $txnid = Str::random(4).time();

                user_wallet_decrement($user->id, $currency_id, $request->amount, $wallet->wallet_type);
                user_wallet_increment($receiver->id, $currency_id, $request->amount, $wallet->wallet_type);

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $request->amount;
                $trans_wallet       = get_wallet($user->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'Send_Money';
                $trans->details     = trans('Send Money');
                $trans->save();


                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $receiver->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $request->amount;
                $trans_wallet       = get_wallet($receiver->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'Recieve_Money';
                $trans->details     = trans('Send Money');
                $trans->save();

                session(['sendstatus'=>1, 'saveData'=>$trans]);
                // user_wallet_decrement($user->id, $currency_id, $request->amount);
                // user_wallet_increment($receiver->id, $currency_id, $request->amount);
                
                if(SaveAccount::whereUserId($user_id)->where('receiver_id',$receiver->id)->exists()){
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Send Successfully.']);
                }

                    $to = $receiver->email;
                    $subject = " Money send successfully.";
                    $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                    sendMail($to,$subject,$msg,$headers);
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Money Send Successfully.']);
            }else{
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Sender not found!.']);

            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function requestmoney(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $rules = [
                'account_name'      => 'required',
                'wallet_id'         => 'required',
                'amount'            => 'required|gt:0'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $user = User::whereId($user_id)->first();

            if($user->bank_plan_id === null){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailyRequests = MoneyRequest::whereUserId($user_id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
            $monthlyRequests = MoneyRequest::whereUserId($user_id)->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');

            $gs = Generalsetting::first();

            if($request->account_number == $user->account_number){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!']);

            }

            $receiver = User::where('account_number',$request->account_number)->first();
            if($receiver === null){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'No register user with this email!']);
            }

            if($dailyRequests > $bank_plan->daily_receive){
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily request limit over.']);
            }

            if($monthlyRequests > $bank_plan->monthly_receive){
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly request limit over.']);
            }

            $cost = $gs->fixed_request_charge + ($request->amount/100) * $gs->percentage_request_charge;
            $finalAmount = $request->amount + $cost;

            $txnid = Str::random(4).time();

            $data = new MoneyRequest();
            $data->user_id =$user_id;
            $data->receiver_id = $receiver->id;
            $data->receiver_name = $receiver->name;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->wallet_id;
            $data->cost = $cost;
            $data->amount = $request->amount;
            $data->status = 0;
            $data->details = $request->details;
            $data->user_type = 1;
            $data->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Request Money Send Successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function approvemoney(Request $request, $id)
    {
        try {
            $user_id = Auth::user()->id;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You must be enable 2FA Security.']);
            $rules = [
                'code' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $user = User::whereId($user_id)->first();

            if($uesr->twofa != 1)
            {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You must be enable 2FA Security.']);
            }


            $ga = new GoogleAuthenticator();
            $secret = $user->go;
            $oneCode = $ga->getCode($secret);

            if ($oneCode != $request->code) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Two factor authentication code is wrong.']);
            }

            $data = MoneyRequest::findOrFail($id);
            $gs = Generalsetting::first();

            $currency_id = Currency::whereIsDefault(1)->first()->id;
            $sender = User::whereId($data->receiver_id)->first();
            $receiver = User::whereId($data->user_id)->first();

            if($data->amount > user_wallet_balance($sender->id, $currency_id)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You don,t have sufficient balance!']);
            }

            $finalAmount = $data->amount - $data->cost;

            user_wallet_decrement($sender->id, $currency_id, $data->amount);
            user_wallet_increment($receiver->id, $currency_id, $finalAmount);

            $data->update(['status'=>1]);

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = $user_id;
            $trans->user_type   = $data->user_type;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans_wallet = get_wallet($sender->id, $currency_id);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->amount      = $data->amount;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'Request_Money';
            $trans->details     = trans('Request Money');

            $trans->save();

            $trans = new Transaction();
            $trans->trnx = $data->transaction_no;
            $trans->user_id     = $receiver->id;
            $trans->user_type   = $data->user_type;
            $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            $trans->amount      = $data->amount;
            $trans_wallet = get_wallet($receiver->id, $currency_id);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'Request_Money';
            $trans->details     = trans('Request Money');

            $trans->save();

                $to = $receiver->email;
                $subject = " Money send successfully.";
                $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                sendMail($to,$subject,$msg,$headers);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Send.']);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function requestcancel(Request $request, $id)
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id) {
            $data = MoneyRequest::findOrFail($id);
            $data->update(['status'=>2]);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Successfully Money Request Cancelled.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function receive(Request $request){
        try {
            $user_id = Auth::user()->id;
            $user = User::whereId($user_id)->first();
            if($user->twofa)
            {
                $data['requests'] = MoneyRequest::orderby('id','desc')->whereReceiverId($user_id)->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Success.', 'data' => $data]);
            }else{

            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You must be enable 2FA Security.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function create(Request $request){
        try {
            $user_id = Auth::user()->id;
            if($user_id)
            {
                $wallets = Wallet::where('user_id',$user_id)->with('currency')->get();
                $data['wallets'] = $wallets;
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Success.', 'data' => $data]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
