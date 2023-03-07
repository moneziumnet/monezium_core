<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Generalsetting;
use App\Models\UserApiCred;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BeneficiaryController extends Controller
{
    public $successStatus = 200;

    public function beneficiaries(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $data['beneficiaries'] = Beneficiary::with('bank')->whereUserId($user_id)->orderBy('id','desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function beneficiariescreate(Request $request)
    {
        try{
            $user_id = Auth::user()->id;

            $rules = [
                'other_bank_id' => 'required',
                'account_number' => 'required',
                'account_name' => 'required',
                'nick_name' => 'required',
                'beneficiary_address' => 'required',
                'beneficiary_bank_address' => 'required',
                'swift_bic' => 'required',
                'national_id_no' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $data = new Beneficiary();
            $input = $request->all();


        $input['user_id'] = $user_id;
        $data->fill($input)->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Beneficiary Added Successfully']);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    public function beneficiariesdetails(Request $request)
    {
        try{
            $user_id = Auth::user()->id;
            $rules = [
                'beneficiary_id'       => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }


            $beneficiary_id = $request->beneficiary_id;
            $data['beneficiaries'] = Beneficiary::whereUserId($user_id)->where('id', $beneficiary_id)->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data'=> $data]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }
}
