<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\MerchantShop;
use App\Models\MerchantWallet;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\File;

class MerchantShopController extends Controller
{


    public function index(){
        try {
            $data['shoplist'] = MerchantShop::where('merchant_id',auth()->id())->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function store(Request $request){
        try {
            $rules = [
                'name' => 'required',
                'url' => 'required',
                'document' => 'required|mimes:doc,docx,pdf',
                'logo' => 'required|mimes:png,gif,jpg'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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
            send_notification($request->merchant_id, 'Merchant Shop has been created by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.merchant.shop.index', $request->merchant_id));
            send_staff_telegram('Merchant Shop has been created by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.merchant.shop.index', $request->merchant_id), 'Merchant Shop');
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Shop has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function edit($id) {
        try {
            $data = MerchantShop::findOrFail($id);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        try {
            $rules = [
                'name' => 'required',
                'url' => 'required',
                'document' => 'mimes:doc,docx,pdf',
                'logo' => 'mimes:png,gif,jpg'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Shop has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
    public function delete($id) {
        try {
            $data = MerchantShop::findOrFail($id);
            $data->delete();
            File::delete('assets/doc/'.$data->document);
            File::delete('assets/images/'.$data->logo);
            $wallets = MerchantWallet::where('shop_id', $id)->get();
            foreach ($wallets as $wallet) {
                $wallet->delete();
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Shop has been deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function view_products($id) {
        try {
            $data['products'] = Product::where('user_id',auth()->id())->where('shop_id', $id)->latest()->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}

