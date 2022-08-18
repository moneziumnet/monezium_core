<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Contract;
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
        $rules = ['title' => 'required'];
        $request->validate($rules);

        $data = new Contract();
        $input = $request->all();
        $data->fill($input)->save();

        return redirect()->back()->with('success','Contract has been created successfully');
    }

    public function view($id) {
        $data = Contract::findOrFail($id);
        return view('user.contract.view', compact('data'));
    }

    public function edit($id) {
        $data = Contract::findOrFail($id);
        return view('user.contract.edit', compact('data'));
    }

    public function update(Request $request, $id) {
        $rules = ['title' => 'required'];
        $request->validate($rules);

        $data = Contract::findOrFail($id);
        $input = $request->all();
        $data->update($input);

        return redirect()->back()->with('success','Contract has been updated successfully');
    }
}

