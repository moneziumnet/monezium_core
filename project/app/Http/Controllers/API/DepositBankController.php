<?php

namespace App\Http\Controllers\API;


use App\Models\Generalsetting;
use App\Models\DepositBank;
use App\Models\Currency;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\PlanDetail;
use App\Classes\GoogleAuthenticator;
use App\Models\PaymentGateway;
use App\Models\Admin;
use App\Models\BankPoolAccount;
use App\Models\SubInsBank;

use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class DepositBankController extends Controller
{

    public function index(){
        try {
            $data['deposits'] = DepositBank::orderby('id','desc')->whereUserId(auth()->id())->where('sub_bank_id', '!=', null)->with('user')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create(){
        try {
            $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->pluck('subbank_id');
            $data['banks'] = SubInsBank::where('status', 1)->get();
            $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;
            $data['user'] = auth()->user();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function bankcurrency($id) {
        try {
            $subbank = SubInsBank::find($id);
            if($subbank->hasGateway()){
                $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->where('subbank_id', $id)->with('currency')->get();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            } else {
                $data['bankaccounts'] = BankPoolAccount::where('bank_id', $id)->with('currency')->get();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request){
        try {
            $user = auth()->user();
            if($user->paymentCheck('Bank Incoming')) {
                if ($user->payment_fa != 'two_fa_google') {
                    if ($user->two_fa_code != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
                else{
                    $googleAuth = new GoogleAuthenticator();
                    $secret = $user->go;
                    $oneCode = $googleAuth->getCode($secret);
                    if ($oneCode != $request->otp_code) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Verification code is not matched.']);
                    }
                }
            }
            $other_bank_limit = Generalsetting::first()->other_bank_limit;
            if ($request->amount >= $other_bank_limit) {
                $rules = [
                    'document' => 'required|mimes:xls,xlsx,pdf,jpg,png,doc,docx'
                ];
            }
            else {
                $rules = [
                    'document' => 'mimes:xls,xlsx,pdf,jpg,png,doc,docx'
                ];
            }


            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $currency = Currency::where('id',$request->currency_id)->first();
            $amountToAdd = $request->amount/getRate($currency);
            $user = auth()->user();
            $subbank = SubInsBank::where('id', $request->bank)->first();
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
            $dailydeposit = DepositBank::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
            $monthlydeposit = DepositBank::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

            if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ]);
            }

             if ($subbank->max_limit == 0) {
                if ( $request->amount < $subbank->min_limit ) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined bank limit range.  Min value is '.$subbank->min_limit ]);
                 }
            }
            else {

                if ( $request->amount < $subbank->min_limit ||  $request->amount > $subbank->max_limit) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Your amount is not in defined bank limit range. Max value is '.$subbank->max_limit.' and Min value is '.$subbank->min_limit]);
                 }
            }

            if($dailydeposit > $global_range->daily_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Daily deposit limit over.']);
            }

            if($monthlydeposit > $global_range->monthly_limit){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Monthly deposit limit over.']);
            }



            $txnid = Str::random(4).time();
            $deposit = new DepositBank();

            if ($file = $request->file('document'))
            {
                $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                $file->move('assets/doc',$name);
                $deposit['document'] = $name;
            }

            $deposit['deposit_number'] = $request->deposit_no;//Str::random(12);
            $deposit['user_id'] = auth()->id();
            $deposit['currency_id'] = $request->currency_id;
            $deposit['amount'] = $request->amount;
            $deposit['method'] = $request->method;
            $deposit['sub_bank_id'] = $request->bank;
            $deposit['txnid'] = $txnid;
            $deposit['details'] = $request->details;
            $deposit['status'] = "pending";
            $deposit->save();
            send_notification(auth()->id(), 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Payment Gateway : ".$request->method."\n Transaction ID : ".$request->deposit_no, route('admin.deposits.bank.index'));
            send_whatsapp(auth()->id(), 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Payment Gateway : ".$request->method."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_telegram(auth()->id(), 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Payment Gateway : ".$request->method."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_staff_telegram('Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$request->amount."\n Payment Gateway : ".$request->method."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');
            $gs =  Generalsetting::findOrFail(1);
            $user = auth()->user();
               mailSend('deposit_request',['amount'=>$request->amount, 'curr' => $currency->code, 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=>$request->method ], $user);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Deposit amount '.$request->amount.' ('.$currency->code.') successfully!']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

}
