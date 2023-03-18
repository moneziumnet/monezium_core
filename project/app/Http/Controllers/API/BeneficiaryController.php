<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\BalanceTransfer;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;

class BeneficiaryController extends Controller
{
    public $successStatus = 200;

    public function index(){
        try {
            $data['beneficiaries'] = Beneficiary::whereUserId(auth()->id())->orderBy('id','desc')->paginate(10);
            $data['logs'] = BalanceTransfer::whereUserId(auth()->id())->whereType('other')->orderBy('id','desc')->paginate(10, ['*'], 'transfer');
            $data['logs']->setPageName('transfer');
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function store(Request $request){
        try {
            $data = new Beneficiary();
            $input = $request->all();

            $input['user_id'] = auth()->user()->id;
            if($request->type == 'RETAIL') {
                $input['name'] =  trim($request->firstname)." ".trim($request->lastname);
            }
            else {
                $input['name'] =  $request->company_name;
            }
            $data->fill($input)->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Beneficiary Added Successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function show($id){
        try {
            $data['data'] = Beneficiary::findOrFail($id);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        try {
            //code...
            $data = Beneficiary::findOrFail($id);
            $input = $request->all();

            $input['user_id'] = auth()->user()->id;
            if($request->type == 'RETAIL') {
                $input['name'] =  trim($request->firstname)." ".trim($request->lastname);
            }
            else {
                $input['name'] =  $request->company_name;
            }
            $data->fill($input)->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Beneficiary has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
}
