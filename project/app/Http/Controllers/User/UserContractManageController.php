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
use Datatables;

class UserContractManageController extends Controller
{
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
        $input = $request->all();
        $data->fill($input)->save();

        return redirect()->back()->with('success','Contract has been created successfully');
    }

    public function view($id) {
        $data = Contract::findOrFail($id);
        $description = $data->description;
        if(strpos($data->description,"{amount}" ) !== false) {
            $description = preg_replace("/{amount}/", $data->amount ,$data->description);
        }

        return view('user.contract.view', compact('data', 'description'));
    }

    public function contract_view($id) {
        $data = Contract::findOrFail(decrypt($id));
        $description = $data->description;
        if(strpos($data->description,"{amount}" ) !== false) {
            $description = preg_replace("/{amount}/", $data->amount ,$data->description);
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
        $input = $request->all();
        $data->update($input);

        return redirect()->back()->with('success','Contract has been updated successfully');
    }
    public function delete($id) {
        $data = Contract::findOrFail($id);
        $data->delete();
        return  redirect()->back()->with('success','Contract has been deleted successfully');
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
        $input = $request->all();
        $data->fill($input)->save();

        return redirect()->back()->with('success','AoA has been created successfully');
    }

    public function aoa_view($id) {
        $data = ContractAoa::findOrFail($id);
        $description = $data->description;
        if(strpos($data->description,"{amount}" ) !== false) {
            $description = preg_replace("/{amount}/", $data->amount ,$data->description);
        }
        return view('user.aoa.view', compact('data', 'description'));
    }

    public function aoa_sign_view($id) {
        $data = ContractAoa::findOrFail(decrypt($id));
        $description = $data->description;
        if(strpos($data->description,"{amount}" ) !== false) {
            $description = preg_replace("/{amount}/", $data->amount ,$data->description);
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
        $input = $request->all();
        $data->update($input);

        return redirect()->back()->with('success','AoA has been updated successfully');
    }

    public function aoa_delete($id) {
        $data = ContractAoa::findOrFail($id);
        $data->delete();
        return  redirect()->back()->with('success','AoA has been deleted successfully');
    }
}

