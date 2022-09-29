<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\BalanceTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use function GuzzleHttp\json_decode;

class BeneficiaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['beneficiaries'] = Beneficiary::whereUserId(auth()->id())->orderBy('id','desc')->paginate(10);
        $data['logs'] = BalanceTransfer::whereUserId(auth()->id())->whereType('other')->orderBy('id','desc')->paginate(10, ['*'], 'transfer');
        $data['logs']->setPageName('transfer');
        return view('user.beneficiaries.index',$data);
    }

    public function create(){
        return view('user.beneficiaries.create');
    }

    public function details($id) {
        $data['item'] = BalanceTransfer::findOrFail($id);
        return view('user.beneficiaries.transfer_detail',$data);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
        ]);

        $data = new Beneficiary();
        $input = $request->all();

        $input['user_id'] = auth()->user()->id;
        $data->fill($input)->save();

        return redirect()->route('user.beneficiaries.index')->with('success','Beneficiary Added Successfully');
    }

    public function show($id){
        $data['data'] = Beneficiary::findOrFail($id);
        return view('user.beneficiaries.show',$data);
    }

    public function edit($id){
        $data['beneficiary'] = Beneficiary::findOrFail($id);
        return view('user.beneficiaries.edit',$data);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required',
        ]);

        $data = Beneficiary::findOrFail($id);
        $input = $request->all();

        $input['user_id'] = auth()->user()->id;
        $data->fill($input)->update();
        return redirect()->route('user.beneficiaries.index')->with('message','Beneficiary has been updated successfully');
    }
}
