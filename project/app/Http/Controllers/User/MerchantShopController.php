<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\MerchantShop;
use App\Models\MerchantWallet;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;

class MerchantShopController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

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
            'document' => 'required|mimes:doc,docx,pdf',
            'logo' => 'required|mimes:png,gif,jpg'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = $validator->getMessageBag()->toArray()['document'][0] ?? $validator->getMessageBag()->toArray()['logo'][0];
            return redirect()->back()->with('error',$message);
        }


        $data = new MerchantShop();
        if ($file = $request->file('document'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
        }

        if ($logo = $request->file('logo'))
        {
            $logo_name = Str::random(8).time().'.'.$logo->getClientOriginalExtension();
            $logo->move('assets/images',$logo_name);
        }

        $data->merchant_id = $request->merchant_id;
        $data->name = $request->name;
        $data->document = $name;
        $data->logo = $logo_name;
        $data->url = $request->url;
        $data->save();
        send_notification($request->merchant_id, 'Merchant Shop has been created by '.auth()->user()->name.' Please check.', route('admin.merchant.shop.index', $request->merchant_id));
        return redirect(route('user.merchant.shop.index'))->with('message','Merchant Shop has been created successfully');
    }

    public function edit($id) {
        $data = MerchantShop::findOrFail($id);
        return view('user.merchant.shop.edit', compact('data'));
    }

    public function update(Request $request, $id) {
        $rules = [
            'name' => 'required',
            'url' => 'required',
            'document' => 'mimes:doc,docx,pdf',
            'logo' => 'mimes:png,gif,jpg'
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
        if ($logo = $request->file('logo'))
        {
            File::delete('assets/images/'.$data->logo);
            $logo_name = Str::random(8).time().'.'.$logo->getClientOriginalExtension();
            $logo->move('assets/images',$logo_name);
            $data->logo = $logo_name;
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
        File::delete('assets/images/'.$data->logo);
        $wallets = MerchantWallet::where('shop_id', $id)->get();
        foreach ($wallets as $wallet) {
            $wallet->delete();
        }
        return  redirect()->back()->with('message','Merchant Shop has been deleted successfully');
    }

    public function view_products($id) {
        $data['products'] = Product::where('user_id',auth()->id())->where('shop_id', $id)->latest()->get();
        return view('user.merchant.shop.view_product', $data);
    }
}

