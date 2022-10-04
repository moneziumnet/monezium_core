<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractAoa;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use \PDF;
use Datatables;

class UserContractManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['contract_view', 'aoa_sign_view', 'contract_sign','aoa_sign', 'export_pdf','export_aoa_pdf']]);
    }
    public function index(){
        $data['contracts'] = Contract::where('user_id',auth()->id())->latest()->paginate(15);
        return view('user.contract.index', $data);
    }

    public function create(){
        $data['userlist'] = User::get();
        $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
        return view('user.contract.create', $data);
    }

    public function store(Request $request){
        $rules = ['title' => 'required'];
        $request->validate($rules);

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

        return redirect(route('user.contract.index'))->with('message','Contract has been created successfully');
    }

    public function view($id) {
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

        return view('user.contract.view', compact('data', 'information'));
    }

    public function contract_view($id, $role) {
        $data = Contract::findOrFail(decrypt($id));
        $role = decrypt($role);
        if(auth()->user()) {
            if ($role == 'contractor' && $data->contractor->email != auth()->user()->email) {
                return redirect(route('user.dashboard'))->with('error', 'You are not contractor of this contract.');
            }

            if ($role == 'client' && $data->beneficiary->email != auth()->user()->email) {
                return redirect(url('/'))->with('error', 'You are not client(beneficiary) of this contract.');
            }
        }
        elseif($role == 'contractor') {
            return redirect(url('/'))->with('error', 'You must login to sign this contract as a contractor');
        }
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
        return view('user.contract.contract', compact('data', 'information', 'role'));
    }

    public function contract_sign(Request $request, $id) {
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
        return back()->with('success', 'You have signed successfully');
    }

    public function edit($id) {
        $data['data'] = Contract::findOrFail($id);
        $data['userlist'] = User::get();
        $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
        return view('user.contract.edit', $data);
    }

    public function update(Request $request, $id) {
        $rules = ['title' => 'required'];
        $request->validate($rules);

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

        return redirect(route('user.contract.index'))->with('success','Contract has been updated successfully');
    }
    public function delete($id) {
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
        return  redirect()->back()->with('success','Contract has been deleted successfully');
    }

    public function export_pdf($id) {
        $contract = Contract::where('id', $id)->first();

        $information = $contract->information ? json_decode($contract->information) : array("" => null);
        foreach ($information as $title => $text) {
            $information->$title = str_replace("{Amount}", $contract->amount, $information->$title);
            foreach (json_decode($contract->default_pattern, True) as $key => $value) {
                if(strpos($text, "{".$key."}" ) !== false) {
                    $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                }
            }
            foreach (json_decode($contract->pattern, True) as $key => $value) {
                if(strpos($text, "{".$key."}" ) !== false) {
                    $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                }
            }
        }

        $pdf = Pdf::loadView('user.export.contract', [
            'data' => $contract,
            'information' => $information
        ]);
        return $pdf->download('contract.pdf');
    }

    public function export_aoa_pdf($id) {
        $contract = ContractAoa::where('id', $id)->first();
        $information = $contract->information ? json_decode($contract->information) : array("" => null);
        foreach ($information as $title => $text) {
            $information->$title = str_replace("{Amount}", $contract->amount, $information->$title);
            foreach (json_decode($contract->default_pattern, True) as $key => $value) {
                if(strpos($text, "{".$key."}" ) !== false) {
                    $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                }
            }
            foreach (json_decode($contract->pattern, True) as $key => $value) {
                if(strpos($text, "{".$key."}" ) !== false) {
                    $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                }
            }
        }

        $pdf = Pdf::loadView('user.export.aoa', [
            'data' => $contract,
            'information' => $information
        ]);
        return $pdf->download('aoa.pdf');
    }

    public function beneficiary_create(Request $request)
    {
        $data = new Beneficiary();
        if($request->email == auth()->user()->email) {
            return back()->with('error', 'You can\'t create the beneficiary with your email');
        }
        $input = $request->all();
        $data->fill($input)->save();
        return back()->with('message', 'You have created new beneficiary successfully, please choose beneficiary list.');
    }

    public function sendToMail(Request $request)
    {
        $contract = Contract::findOrFail($request->contract_id);
        $gs = Generalsetting::first();
        if ($request->role == 'contractor') {
            email([

                'email'   => $request->email,
                "subject" => 'New Contract from '.$gs->from_name,
                'message' => "Hello". $contract->contractor->name.",<br/></br>".

                    "You have received new contract as contractor. <br/>"." The Contract Title is <b>$contract->title</b>."."<br/>Please sign the contract." .".<br/></br>".

                    "New Contract Url"  .": ".route('contract.view',['id' => encrypt($contract->id), 'role' => encrypt('contractor')]) ."<br/>
                "
            ]);
        }
        elseif($request->role == 'client') {

            email([

                'email'   => $request->email,
                "subject" => 'New Contract from '.$gs->from_name,
                'message' => "Hello". $contract->beneficiary->name.",<br/></br>".

                    "You have received new contract as client.  <br/>"." The Contract Title is <b>$contract->title</b>."."<br/>Please sign the contract." .".<br/></br>".

                    "New Contract Url"  .": ".route('contract.view',['id' => encrypt($contract->id), 'role' => encrypt('client')]) ."<br/>
                "
            ]);
        }



        return back()->with('message','The Contract has been sent to the contractor and client.');
    }



    public function aoa_index($id){
        $data['aoa_list'] = ContractAoa::where('contract_id',$id)->latest()->paginate(15);
        $data['id'] = $id;
        return view('user.aoa.index', $data);
    }

    public function aoa_create($id){
        $data['userlist'] = User::get();
        $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
        $data['id'] = $id;
        return view('user.aoa.create',$data);
    }

    public function aoa_store(Request $request, $id){
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

        return redirect(route('user.contract.aoa', $id))->with('message','AoA has been created successfully');
    }

    public function aoa_view($id) {
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
        return view('user.aoa.view', compact('data', 'information'));
    }

    public function aoa_sign_view($id,$role) {
        $data = ContractAoa::findOrFail(decrypt($id));
        $role = decrypt($role);
        if(auth()->user()) {
            if ($role == 'contractor' && $data->contractor->email != auth()->user()->email) {
                return redirect(route('user.dashboard'))->with('error', 'You are not contractor of this contract.');
            }

            if ($role == 'client' && $data->beneficiary->email != auth()->user()->email) {
                return redirect(url('/'))->with('error', 'You are not client(beneficiary) of this contract.');
            }
        }
        elseif($role == 'contractor') {
            return redirect(url('/'))->with('error', 'You must login to sign this contract as a contractor');
        }
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
                if(strpos($text, "{".$key."}" ) !==  false) {
                    $information->$title = str_replace("{".$key."}", $value ,$information->$title);
                }
            }
        }
        return view('user.aoa.aoa', compact('data', 'information', 'role'));
    }

    public function aoa_sign(Request $request, $id) {
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
        return back()->with('success', 'You have signed successfully');
    }

    public function aoa_edit($id) {
        $data['data'] = ContractAoa::findOrFail($id);
        $data['userlist'] = User::get();
        $data['clientlist'] = Beneficiary::where('user_id', auth()->id())->get();
        return view('user.aoa.edit', $data);
    }

    public function aoa_update(Request $request, $id) {
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

        return redirect(route('user.contract.aoa', $request->contract_id))->with('message','AoA has been updated successfully');
    }

    public function aoa_delete($id) {
        $data = ContractAoa::findOrFail($id);
        $data->delete();
        File::delete('assets/images/'.$data->contracter_image_path);
        File::delete('assets/images/'.$data->customer_image_path);

        return  redirect()->back()->with('success','AoA has been deleted successfully');
    }

    public function aoa_sendToMail(Request $request)
    {
        $contract = ContractAoa::findOrFail($request->contract_id);
        $gs = Generalsetting::first();
        if ($request->role == 'contractor') {
            email([

                'email'   => $request->email,
                "subject" => 'New AoA from '.$gs->from_name,
                'message' => "Hello". $contract->contractor->name.",<br/></br>".

                    "You have received new AoA as contractor. <br/>"." The AoA Title is <b>$contract->title</b>."."<br/>Please sign the AoA." .".<br/></br>".

                    "New AoA Url"  .": ".route('aoa.view',['id' => encrypt($contract->id), 'role' => encrypt('contractor')]) ."<br/>
                "
            ]);
        }
        elseif($request->role == 'client') {

            email([

                'email'   => $request->email,
                "subject" => 'New Contract from '.$gs->from_name,
                'message' => "Hello". $contract->beneficiary->name.",<br/></br>".

                    "You have received new AoA as client.  <br/>"." The AoA Title is <b>$contract->title</b>."."<br/>Please sign the AoA." .".<br/></br>".

                    "New AoA Url"  .": ".route('aoa.view',['id' => encrypt($contract->id), 'role' => encrypt('client')]) ."<br/>
                "
            ]);
        }

        return back()->with('message','The AoA has been sent to the contractor and client.');
    }
}

