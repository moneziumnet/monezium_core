<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\MerchantShop;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;

class MerchantShopController extends Controller
{
    public function index(){
        $data['shoplist'] = MerchantShop::where('merchant_id',auth()->id())->latest()->paginate(15);
        return view('user.merchant.shop.index', $data);
    }

    public function create(){
        return view('user.merchant.shop.create');
    }

    public function store(Request $request){
        $rules = [
            'name' => 'required',
            'url' => 'required',
            'document' => 'required|mimes:doc,docx,pdf'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['document'][0]);
        }


        $data = new MerchantShop();
        if ($file = $request->file('document'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
        }
        $data->merchant_id = $request->merchant_id;
        $data->name = $request->name;
        $data->document = $name;
        $data->url = $request->url;
        $data->save();
        return redirect()->back()->with('message','Merchant Shop has been created successfully');
    }

    public function edit($id) {
        $data = MerchantShop::findOrFail($id);
        return view('user.merchant.shop.edit', compact('data'));
    }

    public function update(Request $request, $id) {
        $rules = [
            'name' => 'required',
            'url' => 'required',
            'document' => 'mimes:doc,docx,pdf'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['document'][0]);
        }

        $data = MerchantShop::findOrFail($id);

        if ($file = $request->file('document'))
        {
            File::delete('assets/doc/'.$data->document);
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
            $data->document = $name;
        }
        $data->merchant_id = $request->merchant_id;
        $data->name = $request->name;
        $data->url = $request->url;

        $data->update();

        return redirect()->back()->with('message','Merchant Shop has been updated successfully');
    }
    public function delete($id) {
        $data = MerchantShop::findOrFail($id);
        $data->delete();
        File::delete('assets/doc/'.$data->document);
        return  redirect()->back()->with('message','Merchant Shop has been deleted successfully');
    }
}

