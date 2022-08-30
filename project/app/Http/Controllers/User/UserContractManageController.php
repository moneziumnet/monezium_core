<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractAoa;
use App\Models\Generalsetting;
use App\Models\User;
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
        return view('user.contract.create');
    }

    public function store(Request $request){
        $rules = ['title' => 'required', 'description' => 'required'];
        $request->validate($rules);

        $data = new Contract();
        $data->title = $request->title;
        $data->description = $request->description;
        $data->user_id = $request->user_id;
        $items = array_combine($request->item,$request->value);
        $data->pattern = json_encode($items);
        $data->save();

        return redirect()->back()->with('success','Contract has been created successfully');
    }

    public function view($id) {
        $data = Contract::findOrFail($id);
        $description = $data->description;
        foreach (json_decode($data->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }

        return view('user.contract.view', compact('data', 'description'));
    }

    public function contract_view($id) {
        $data = Contract::findOrFail(decrypt($id));
        $description = $data->description;
        foreach (json_decode($data->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }
        return view('user.contract.contract', compact('data', 'description'));
    }

    public function contract_sign(Request $request, $id) {
        $data = Contract::findOrFail($id);

        $folderPath ='assets/images/';
        $image_parts = explode(";base64,", $request->signed);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $filename = uniqid() . '.'.$image_type;
        $file = $folderPath . $filename;
        file_put_contents($file, $image_base64);
        $data->status = 1;
        $data->image_path = $filename;
        $data->update();
        return back()->with('success', 'You have signed successfully');
    }

    public function edit($id) {
        $data = Contract::findOrFail($id);
        return view('user.contract.edit', compact('data'));
    }

    public function update(Request $request, $id) {
        $rules = ['title' => 'required', 'description' => 'required'];
        $request->validate($rules);

        $data = Contract::findOrFail($id);
        $data->title = $request->title;
        $data->description = $request->description;
        $data->user_id = $request->user_id;
        $items = array_combine($request->item,$request->value);
        $data->pattern = json_encode($items);
        $data->update();

        return redirect()->back()->with('success','Contract has been updated successfully');
    }
    public function delete($id) {
        $data = Contract::findOrFail($id);
        $data->delete();
        File::delete('assets/images/'.$data->image_path);
        return  redirect()->back()->with('success','Contract has been deleted successfully');
    }

    public function export_pdf($id) {
        $contract = Contract::where('id', $id)->first();
        $description = $contract->description;
        foreach (json_decode($contract->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }

        $pdf = Pdf::loadView('user.export.contract', [
            'data' => $contract,
            'description' => $description
        ]);
        return $pdf->download('contract.pdf');
    }

    public function export_aoa_pdf($id) {
        $contract = ContractAoa::where('id', $id)->first();
        $description = $contract->description;
        foreach (json_decode($contract->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }

        $pdf = Pdf::loadView('user.export.aoa', [
            'data' => $contract,
            'description' => $description
        ]);
        return $pdf->download('aoa.pdf');
    }





    public function aoa_index($id){
        $data['aoa_list'] = ContractAoa::where('contract_id',$id)->latest()->paginate(15);
        $data['id'] = $id;
        return view('user.aoa.index', $data);
    }

    public function aoa_create($id){
        return view('user.aoa.create',compact('id'));
    }

    public function aoa_store(Request $request, $id){
        $rules = ['title' => 'required', 'description' => 'required'];
        $request->validate($rules);

        $data = new ContractAoa();
        $folderPath ='assets/images/';
        $image_parts = explode(";base64,", $request->signed);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $filename = uniqid() . '.'.$image_type;
        $file = $folderPath . $filename;
        file_put_contents($file, $image_base64);

        $data->title = $request->title;
        $data->description = $request->description;
        $data->contract_id = $request->contract_id;
        $items = array_combine($request->item,$request->value);
        $data->pattern = json_encode($items);
        $data->contracter_image_path = $filename;
        $data->save();

        return redirect()->back()->with('success','AoA has been created successfully');
    }

    public function aoa_view($id) {
        $data = ContractAoa::findOrFail($id);
        $description = $data->description;
        foreach (json_decode($data->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }
        return view('user.aoa.view', compact('data', 'description'));
    }

    public function aoa_sign_view($id) {
        $data = ContractAoa::findOrFail(decrypt($id));
        $description = $data->description;
        foreach (json_decode($data->pattern, True) as $key => $value) {
            if(strpos($description, "{".$key."}" ) != false) {
                $description = preg_replace("/{".$key."}/", $value ,$description);
            }
        }
        return view('user.aoa.aoa', compact('data', 'description'));
    }

    public function aoa_sign(Request $request, $id) {
        $data = ContractAoa::findOrFail($id);

        $folderPath ='assets/images/';
        $image_parts = explode(";base64,", $request->signed);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $filename = uniqid() . '.'.$image_type;
        $file = $folderPath . $filename;
        file_put_contents($file, $image_base64);
        $data->status = 1;
        $data->customer_image_path = $filename;
        $data->update();
        return back()->with('success', 'You have signed successfully');
    }

    public function aoa_edit($id) {
        $data = ContractAoa::findOrFail($id);
        return view('user.aoa.edit', compact('data'));
    }

    public function aoa_update(Request $request, $id) {
        $rules = ['title' => 'required', 'description' => 'required'];
        $request->validate($rules);

        $data = ContractAoa::findOrFail($id);

        $folderPath ='assets/images/';
        $image_parts = explode(";base64,", $request->signed);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $filename = uniqid() . '.'.$image_type;
        $file = $folderPath . $filename;
        file_put_contents($file, $image_base64);

        File::delete('assets/images/'.$data->contracter_image_path);


        $data->title = $request->title;
        $data->description = $request->description;
        $data->contract_id = $request->contract_id;
        $items = array_combine($request->item,$request->value);
        $data->pattern = json_encode($items);
        $data->contracter_image_path = $filename;
        $data->update();


        return redirect()->back()->with('success','AoA has been updated successfully');
    }

    public function aoa_delete($id) {
        $data = ContractAoa::findOrFail($id);
        $data->delete();
        File::delete('assets/images/'.$data->contracter_image_path);
        File::delete('assets/images/'.$data->customer_image_path);

        return  redirect()->back()->with('success','AoA has been deleted successfully');
    }
}

