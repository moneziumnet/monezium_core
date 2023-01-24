<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\User;
use App\Models\Transaction;
use App\Models\BankGateway;
use App\Models\BalanceTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

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
        return redirect()->route('user.beneficiaries.index')->with('message','Beneficiary has been updated successfully');
    }

    public function update_beneficiary_db() {
        $data = Beneficiary::all();
        foreach ($data as $value) {
            # code...
            $value->bank_name = Crypt::encryptString($value->bank_name);
            $value->address = Crypt::encryptString($value->address);
            $value->bank_address = Crypt::encryptString($value->bank_address);
            $value->swift_bic = Crypt::encryptString($value->swift_bic);
            $value->account_iban = Crypt::encryptString($value->account_iban);
            $value->name = Crypt::encryptString($value->name);
            $value->email = Crypt::encryptString($value->email);
            $value->phone = Crypt::encryptString($value->phone);
            $value->registration_no = Crypt::encryptString($value->registration_no);
            $value->vat_no = Crypt::encryptString($value->vat_no);
            $value->contact_person = Crypt::encryptString($value->contact_person);
            $value->update();
        }
        return response()->json('success');
    }
    public function update_user_db() {
        $data = User::all();
        foreach ($data as $value) {
            # code...
            $value->name = Crypt::encryptString($value->name);
            $value->zip =$value->zip ? Crypt::encryptString($value->zip) : null;
            $value->city =$value->city ? Crypt::encryptString($value->city) : null;
            $value->address = $value->address ? Crypt::encryptString($value->address) : null;
            $value->street = $value->street ? Crypt::encryptString($value->street) : null;
            $value->phone = $value->phone ? Crypt::encryptString($value->phone) : null;
            $value->vat = $value->vat ? Crypt::encryptString($value->vat) : null;
            $value->email = $value->email ? Crypt::encryptString($value->email) : null;
            $value->company_name = $value->company_name ? Crypt::encryptString($value->company_name) : null;
            $value->company_type = $value->company_type ? Crypt::encryptString($value->company_type) : null;
            $value->company_reg_no = $value->company_reg_no ? Crypt::encryptString($value->company_reg_no) : null;
            $value->company_vat_no = $value->company_vat_no ? Crypt::encryptString($value->company_vat_no) : null;
            $value->company_address = $value->company_address ? Crypt::encryptString($value->company_address) : null;
            $value->company_city = $value->company_city ? Crypt::encryptString($value->company_city) : null;
            $value->personal_code = $value->personal_code ? Crypt::encryptString($value->personal_code) : null;
            $value->your_id = $value->your_id ? Crypt::encryptString($value->your_id) : null;
            $value->update();
        }
        return response()->json('success');
    }
    public function update_tr_db() {
        $data = Transaction::all();
        foreach ($data as $value) {
            # code...
            $value->data = Crypt::encryptString($value->data);
            $value->update();
        }
        return response()->json('success');
    }
    public function update_bank_db() {
        $data = BankGateway::all();
        foreach ($data as $value) {
            # code...
            $value->information = Crypt::encryptString($value->information);
            $value->update();
        }
        return response()->json('success');
    }
}
