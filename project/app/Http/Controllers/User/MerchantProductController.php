<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\MerchantShop;
use App\Models\Product;
use App\Models\Currency;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\MerchantWallet;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;

class MerchantProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['products'] = Product::where('user_id',auth()->id())->latest()->paginate(15);
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->whereStatus(1)->get();
        $data['categories'] = ProductCategory::where('user_id', auth()->id())->get();
        $data['currencies'] = Currency::whereStatus(1)->get();
        return view('user.merchant.product.index', $data);
    }

    public function store(Request $request){
        $rules = [
            'image' => 'required|mimes:jpg,git,png'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['image'][0]);
        }


        $data = new Product();
        if ($file = $request->file('image'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
        }
        $input = $request->all();
        $input['ref_id'] ='PT-'.Str::random(6);
        $data->fill($input)->save();
        $image = new ProductImage();
        $image->product_id = $data->id;
        $image->image = $name;
        $image->save();
         return redirect()->back()->with('message','New Product has been created successfully');
    }

    public function edit($id) {
        $data['data'] = Product::findOrFail($id);
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->whereStatus(1)->get();
        $data['categories'] = ProductCategory::where('user_id', auth()->id())->get();
        $data['currencies'] = Currency::whereStatus(1)->get();
        return view('user.merchant.product.edit', $data);
    }

    public function update(Request $request, $id) {
        $rules = [
            'image' => 'mimes:jpg,git,png'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['image'][0]);
        }

        $data = Product::findOrFail($id);
        $input = $request->all();
        $image = ProductImage::where('product_id', $data->id)->first();
        if ($file = $request->file('image'))
        {
            File::delete('assets/images/'.$image->image);
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            $image->image = $name;
        }
        $image->update();

        $data->fill($input)->update();

        return redirect()->route('user.merchant.product.index')->with('message','Merchant Product has been updated successfully');
    }

    public function delete($id) {
        $data = Product::findOrFail($id);
        $image = ProductImage::where('product_id', $data->id)->first();
        File::delete('assets/images/'.$image->image);
        $image->delete();
        $data->delete();
        return  redirect()->back()->with('message','Merchant Product has been deleted successfully');
    }
}

