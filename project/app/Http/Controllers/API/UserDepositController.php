<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Classes\GeniusMailer;
use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserDepositController extends Controller
{
    public $successStatus = 200;

    public function deposit(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $data['deposits'] = Deposit::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    
    
    
    public function depositdetails(Request $request)
    {
        try{
            $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;
            $rules = [
                'user_deposit_id'       => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $user_deposit_id = $request->user_deposit_id;

            $data['deposits'] = Deposit::whereUserId($user_id)->where('id',$user_deposit_id)->first();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
