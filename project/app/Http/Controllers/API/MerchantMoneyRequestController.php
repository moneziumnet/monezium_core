<?php

namespace App\Http\Controllers\API;

use Validator;

use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\MoneyRequest;

use App\Models\Generalsetting;
use App\Models\MerchantShop;
use App\Models\PlanDetail;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;

class MerchantMoneyRequestController extends Controller
{

    public function index(){
        try {
            $data['requests'] = MoneyRequest::orderby('id','desc')->whereUserId(auth()->id())->where('user_type', 2)->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create(){
        try {
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['shop_list'] = MerchantShop::whereStatus(1)->where('merchant_id', auth()->id())->get();
            $data['wallets'] = $wallets;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request){
        try {
            $rules = [
                'account_name' => 'required',
                'wallet_id' => 'required',
                'amount' => 'required|gt:0',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }


            $user = auth()->user();

            if($user->bank_plan_id === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to buy a plan to withdraw.']);
            }

            if(now()->gt($user->plan_end_date)){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Plan Date Expired.']);
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailyRequests = MoneyRequest::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
            $monthlyRequests = MoneyRequest::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'recieve')->first();
            $gs = Generalsetting::first();
            $currency = Curreny::findOrFail($request->wallet_id);
            $rate = getRate($currency);

            if($request->email == $user->email){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not send money yourself!']);
            }

            $receiver = User::where('email',$request->email)->first();
            if($receiver === null){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'No register user with this email!']);
            }

            if($dailyRequests > $global_range->daily_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily request limit over.']);
            }

            if($monthlyRequests > $global_range->monthly_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly request limit over.']);
            }

            if ($request->amount/$rate < $global_range->min || $request->amount/$rate > $global_range->max) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ]);
            }

            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'recieve');
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/(100*$rate)) * $transaction_global_fee->data->percent_charge;

            if($user->referral_id != 0)
            {
                $transaction_custom_cost = 0;
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'recieve');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/(100*$rate)) * $transaction_custom_fee->data->percent_charge;
                }
            }



            $txnid = Str::random(4).time();

            $data = new MoneyRequest();
            $data->user_id = auth()->user()->id;
            $data->receiver_id = $receiver->id;
            $data->receiver_name = $receiver->name;
            $data->transaction_no = $txnid;
            $data->currency_id = $request->wallet_id;
            $data->cost = $transaction_global_cost*$rate;
            $data->supervisor_cost = $user->referral_id != 0 ? $transaction_custom_cost*$rate : 0 ;
            $data->amount = $request->amount;
            $data->status = 0;
            $data->details = $request->details;
            $data->user_type = 2;
            $data->shop_id = $request->shop_id;
            $data->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Request Money Send Successfully.']);
            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function details($id){
        try {
            $data = MoneyRequest::findOrFail($id);
            $from = User::whereId($data->user_id)->first();
            $to = User::whereId($data->receiver_id)->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data','from','to')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
