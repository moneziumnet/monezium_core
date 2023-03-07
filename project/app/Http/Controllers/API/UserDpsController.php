<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\UserDps;
use App\Models\DpsPlan;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserDpsController extends Controller
{
    public $successStatus = 200;
/***DPS API**/
    
    public function dpsdetails(Request $request, $id)
    {
        try {
            $user_id = Auth::user()->id;

            if($id)
            {
                $dps = UserDps::whereUserId($user_id)->where('id',$id)->orderby('id','desc')->first();
                if(!empty($dps))
                {
                    $data['dps'] = $dps;
                    return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
                }else{
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This DPS is not yours.']);
                }
                
            }else{
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please pass the valid User DPS ID']);
            }
            
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
    public function dps_index(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['dps'] = UserDps::whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function runningdps(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['dps'] = UserDps::whereStatus(1)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function matureddps(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $data['dps'] = UserDps::whereStatus(2)->whereUserId($user_id)->orderby('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function dpsplan(Request $request)
    {
        try {
            $data['plans']          = DpsPlan::whereStatus(1)->orderby('id','desc')->paginate(10);
            $data['currencylist']   = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
/**END DPS API**/


}
