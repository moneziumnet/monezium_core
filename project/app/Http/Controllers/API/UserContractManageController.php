<?php

namespace App\Http\Controllers\API;


use App\Models\Contract;
use App\Models\ContractAoa;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Beneficiary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use \PDF;
use Auth;

class UserContractManageController extends Controller
{

    public function index(){
        try {
            $data['contracts'] = Contract::where('user_id',auth()->id())->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create(){
        try {
            $data['userlist'] = User::get();
            $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request){
        try {

            $rules = ['title' => 'required'];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $data = new Contract();
            $data->title = $request->title;
            $data->information = json_encode(array_combine($request->desc_title,$request->desc_text));
            $data->user_id = $request->user_id;
            $data->contractor_id = $request->contractor_id;

            $data->amount = $request->amount;

            if(isset($request->item)){
                $items = array_combine($request->item,$request->value);
                $data->pattern = json_encode($items);
            }
            if(isset($request->default_item)){
                $default_items = array_combine($request->default_item,$request->default_value);
                $data->default_pattern = json_encode($default_items);
            }

            $contractor_info = explode(' ', $request->contractor);
            if(count($contractor_info) == 2) {
                $data->contractor_type = 'App\\Models\\'.$contractor_info[0];
                $data->contractor_id = $contractor_info[1];
            }
            $client_info = explode(' ', $request->client);
            if(count($client_info) == 2) {
                $data->client_type = 'App\\Models\\'.$client_info[0];
                $data->client_id = $client_info[1];
            }
            $data->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Contract has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function view($id) {
        try {
            $data = Contract::findOrFail($id);
            $information = $data->information ? json_decode($data->information) : array("" => null);
            foreach ($information as $title => $text) {
                $information->$title = str_replace("{Amount}", $data->amount, $information->$title);
                if(isset($data->default_pattern)){
                    foreach (json_decode($data->default_pattern, True) as $key => $value) {
                        if(strpos($text, "{".$key."}" ) !== false) {
                            $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                        }
                    }
                }
                foreach (json_decode($data->pattern, True) as $key => $value) {
                    if(strpos($text, "{".$key."}" ) !== false) {
                        $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                    }
                }
            }

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data', 'information')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }


    public function contract_sign(Request $request, $id) {
        try {
            $data = Contract::findOrFail($id);
            if( $request->sign_path) {
                if($request->role == 'client') {
                    @unlink('assets/images/'.$data->customer_image_path);
                    $data->customer_image_path = $request->sign_path;

                }
                elseif ($request->role == 'contractor') {
                    @unlink('assets/images/'.$data->contracter_image_path);
                    $data->contracter_image_path = $request->sign_path;
                }
            }
            else {
                $folderPath ='assets/images/';
                $image_parts = explode(";base64,", $request->signed);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $filename = uniqid() . '.'.$image_type;
                $file = $folderPath . $filename;
                file_put_contents($file, $image_base64);
                if($request->role == 'client') {
                    @unlink('assets/images/'.$data->customer_image_path);
                    $data->customer_image_path = $filename;

                }
                elseif ($request->role == 'contractor') {
                    @unlink('assets/images/'.$data->contracter_image_path);
                    $data->contracter_image_path = $filename;
                }
            }
            if ($data->contracter_image_path && $data->customer_image_path) {
                $data->status = 1;
            }
            $data->update();
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have signed successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function edit($id) {

        try {
            $data['data'] = Contract::findOrFail($id);
            $data['userlist'] = User::get();
            $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        try {
            $rules = ['title' => 'required'];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $data = Contract::findOrFail($id);
            $data->title = $request->title;
            $data->amount = $request->amount;
            $data->information = json_encode(array_combine($request->desc_title,$request->desc_text));
            $data->user_id = $request->user_id;
            $contractor_info = explode(' ', $request->contractor);
            if(count($contractor_info) == 2) {
                $data->contractor_type = 'App\\Models\\'.$contractor_info[0];
                $data->contractor_id = $contractor_info[1];
            }
            $client_info = explode(' ', $request->client);
            if(count($client_info) == 2) {
                $data->client_type = 'App\\Models\\'.$client_info[0];
                $data->client_id = $client_info[1];
            }
            if(isset($request->item)){
                $items = array_combine($request->item,$request->value);
                $data->pattern = json_encode($items);
            }
            if(isset($request->default_item)){
                $default_items = array_combine($request->default_item,$request->default_value);
                $data->default_pattern = json_encode($default_items);
            }
            $data->update();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Contract has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
    public function delete($id) {
        try {
            $data = Contract::findOrFail($id);
            $data->delete();
            File::delete('assets/images/'.$data->contracter_image_path);
            File::delete('assets/images/'.$data->customer_image_path);
            $aoa =  ContractAoa::where('contract_id', $id)->get();
            foreach ($aoa as $key => $value) {
                $aoa_data = ContractAoa::findOrFail($value->id);
                $aoa_data->delete();
                File::delete('assets/images/'.$aoa_data->contracter_image_path);
                File::delete('assets/images/'.$aoa_data->customer_image_path);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Contract has been deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function sendToMail(Request $request)
    {
        try {
            $contract = Contract::findOrFail($request->contract_id);
            $gs = Generalsetting::first();
            if ($request->role == 'contractor') {
                $msg = "Hello". $contract->contractor->name.",<br>"."You have received new contract as contractor. <br>"." The Contract Title is <b>$contract->title</b>."."<br>Please sign the contract." .".<br>"."New Contract Url"  .": ".route('contract.view',['id' => encrypt($contract->id), 'role' => encrypt('contractor')]) ."<br>";
                $gs = Generalsetting::first();
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                sendMail($request->email, 'New Contract from '.$contract->beneficiary->name, $msg, $headers);
            }
            elseif($request->role == 'client') {
                $gs = Generalsetting::first();
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $msg = "Hello". $contract->beneficiary->name.",<br>"."You have received new contract as client.  <br>"." The Contract Title is <b>$contract->title</b>."."<br>Please sign the contract." .".<br>"."New Contract Url"  .": ".route('contract.view',['id' => encrypt($contract->id), 'role' => encrypt('client')]) ."<br>";
                sendMail($request->email, 'New Contract from '.$contract->contractor->name, $msg, $headers);

            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'The Contract has been sent to the contractor and client.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_index($id){
        try {
            $data['aoa_list'] = ContractAoa::where('contract_id',$id)->latest()->paginate(15);
            $data['id'] = $id;
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_create($id){
        try {
            $data['userlist'] = User::get();
            $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
            $data['id'] = $id;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_store(Request $request, $id){
        try {
            $rules = ['title' => 'required'];
            $request->validate($rules);

            $data = new ContractAoa();
            $contractor_info = explode(' ', $request->contractor);
            if(count($contractor_info) == 2) {
                $data->contractor_type = 'App\\Models\\'.$contractor_info[0];
                $data->contractor_id = $contractor_info[1];
            }
            $client_info = explode(' ', $request->client);
            if(count($client_info) == 2) {
                $data->client_type = 'App\\Models\\'.$client_info[0];
                $data->client_id = $client_info[1];
            }

            $data->title = $request->title;
            $data->amount = $request->amount;
            $data->information = json_encode(array_combine($request->desc_title,$request->desc_text));
            $data->contract_id = $request->contract_id;
            if(isset($request->item)){
                $items = array_combine($request->item,$request->value);
                $data->pattern = json_encode($items);
            }
            if(isset($request->default_item)){
                $default_items = array_combine($request->default_item,$request->default_value);
                $data->default_pattern = json_encode($default_items);
            }
            $data->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'AoA has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_view($id) {
        try {
            $data = ContractAoa::findOrFail($id);
            $information = $data->information ? json_decode($data->information) : array("" => null);
            foreach ($information as $title => $text) {
                $information->$title = str_replace("{Amount}", $data->amount, $information->$title);
                if(isset($data->default_pattern)){
                    foreach (json_decode($data->default_pattern, True) as $key => $value) {
                        if(strpos($text, "{".$key."}" ) !== false) {
                            $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                        }
                    }
                }
                foreach (json_decode($data->pattern, True) as $key => $value) {
                    if(strpos($text, "{".$key."}" ) !== false) {
                        $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                    }
                }
            }
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'success', 'data' => compact('data', 'information')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function aoa_sign(Request $request, $id) {
        try {
            $data = ContractAoa::findOrFail($id);
            if( $request->sign_path) {
                if($request->role == 'client') {
                    @unlink('assets/images/'.$data->customer_image_path);
                    $data->customer_image_path = $request->sign_path;

                }
                elseif ($request->role == 'contractor') {
                    @unlink('assets/images/'.$data->contracter_image_path);
                    $data->contracter_image_path = $request->sign_path;
                }
            }
            else {
                $folderPath ='assets/images/';
                $image_parts = explode(";base64,", $request->signed);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $filename = uniqid() . '.'.$image_type;
                $file = $folderPath . $filename;
                file_put_contents($file, $image_base64);
                if($request->role == 'client') {
                    @unlink('assets/images/'.$data->customer_image_path);
                    $data->customer_image_path = $filename;

                }
                elseif ($request->role == 'contractor') {
                    @unlink('assets/images/'.$data->contracter_image_path);
                    $data->contracter_image_path = $filename;
                }
            }
            if ($data->contracter_image_path && $data->customer_image_path) {
                $data->status = 1;
            }
            $data->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have signed successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_edit($id) {
        try {
            $data['data'] = ContractAoa::findOrFail($id);
            $data['userlist'] = User::get();
            $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_update(Request $request, $id) {
        try {
            $rules = ['title' => 'required'];
            $request->validate($rules);

            $data = ContractAoa::findOrFail($id);
            $contractor_info = explode(' ', $request->contractor);
            if(count($contractor_info) == 2) {
                $data->contractor_type = 'App\\Models\\'.$contractor_info[0];
                $data->contractor_id = $contractor_info[1];
            }
            $client_info = explode(' ', $request->client);
            if(count($client_info) == 2) {
                $data->client_type = 'App\\Models\\'.$client_info[0];
                $data->client_id = $client_info[1];
            }
            $data->title = $request->title;
            $data->amount = $request->amount;
            $data->information = json_encode(array_combine($request->desc_title,$request->desc_text));
            $data->contract_id = $request->contract_id;
            if(isset($request->item)){
                $items = array_combine($request->item,$request->value);
                $data->pattern = json_encode($items);
            }
            if(isset($request->default_item)){
                $default_items = array_combine($request->default_item,$request->default_value);
                $data->default_pattern = json_encode($default_items);
            }
            $data->update();

            return response()->json(['status' => '200', 'error_code' => '0', 'AoA has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_delete($id) {
        try {
            $data = ContractAoa::findOrFail($id);
            $data->delete();
            File::delete('assets/images/'.$data->contracter_image_path);
            File::delete('assets/images/'.$data->customer_image_path);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'AoA has been deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function aoa_sendToMail(Request $request)
    {
        try {
            $contract = ContractAoa::findOrFail($request->contract_id);
            $gs = Generalsetting::first();
            if ($request->role == 'contractor') {

                $gs = Generalsetting::first();
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $msg = "Hello". $contract->contractor->name.",<br>"."You have received new AoA as contractor. <br>"." The AoA Title is <b>$contract->title</b>."."<br/>Please sign the AoA." .".<br>"."New AoA Url"  .": ".route('aoa.view',['id' => encrypt($contract->id), 'role' => encrypt('contractor')]) ."<br>";
                sendMail($request->email, 'New Contract from '.$contract->beneficiary->name, $msg, $headers);

            }
            elseif($request->role == 'client') {

                $gs = Generalsetting::first();
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                $headers .= "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $msg = "Hello". $contract->beneficiary->name.",<br>"."You have received new AoA as client.  <br>"." The AoA Title is <b>$contract->title</b>."."<br>Please sign the AoA." .".<br>"."New AoA Url"  .": ".route('aoa.view',['id' => encrypt($contract->id), 'role' => encrypt('client')]) ."<br>";
                sendMail($request->email, 'New Contract from '.$contract->contractor->name, $msg, $headers);

            }

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'The AoA has been sent to the contractor and client.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}

