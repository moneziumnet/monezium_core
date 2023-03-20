<?php

namespace App\Http\Controllers\API;


use App\Models\MerchantShop;
use App\Models\Product;
use App\Models\Currency;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\MerchantWallet;
use App\Models\Campaign;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Charge;
use App\Models\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Auth;

class UserShopController extends Controller
{

    public function index(){
        try {
            $data['products'] = Product::where('user_id','!=',auth()->id())->wherestatus(1)->get();
            $data['campaigns'] = Campaign::where('user_id','!=',auth()->id())->wherestatus(1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function order($id) {
        try {
            $data = Product::where('id', $id)->first();
            $bankaccounts = BankAccount::where('user_id', $data->user_id)->where('currency_id', $data->currency_id)->get();
            $crypto_ids =  MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $data->shop_id)->pluck('currency_id');
            $cryptolist = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids->toArray())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data', 'bankaccounts', 'cryptolist')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function donate($id) {
        try {
            $data = Campaign::where('id', $id)->first();
            $bankaccounts = BankAccount::where('user_id', $data->user_id)->where('currency_id', $data->currency_id)->get();
            $crypto_ids =  Wallet::where('user_id', $data->user_id)->where('user_type',1)->where('wallet_type', 8)->pluck('currency_id');
            $cryptolist= Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids->toArray())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('data', 'bankaccounts', 'cryptolist')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

}

