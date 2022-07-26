<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Generalsetting;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\DepositBank;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserDepositBankController extends Controller
{
    public $successStatus = 200;

    public function depositsbank(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['deposits'] = DepositBank::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    
    public function depositbankcreate(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

            $rules = [
                'subinstitude_id' => 'required',
                'txnid'       => 'required',
                'currency_id'       => 'required',
                'method_id'       => 'required',
                'amount'          => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $currency = Currency::where('id',$request->currency_id)->first();
            $amountToAdd = $request->amount/$currency->rate;
            $txnid = Str::random(4).time();
            $deposit = new DepositBank();
            $deposit['deposit_number'] = Str::random(12);
            $deposit['user_id'] = $user_id;
            $deposit['currency_id'] = $request->currency_id;
            $deposit['amount'] = $amountToAdd;
            $deposit['method'] = $request->method_id;
            $deposit['txnid'] = $request->txnid;
            $deposit['status'] = "pending";
            $deposit->save();


            $gs =  Generalsetting::findOrFail(1);
            $user = User::whereId($user_id)->first();
            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $user->email,
                    'type' => "Deposit",
                    'cname' => $user->name,
                    'oamount' => $amountToAdd,
                    'aname' => "",
                    'aemail' => "",
                    'wtitle' => "",
                ];

                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($data);
            }
            else
            {
                $to = $user->email;
                $subject = " You have deposited successfully.";
                $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($to,$subject,$msg,$headers);
            }

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Deposit amount '.$request->amount.' ('.$currency->code.') successfully!']);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    
    
    
    public function depositgateways(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['deposits'] = Deposit::whereUserId($user_id)->orderBy('id','desc')->paginate(10); 
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
