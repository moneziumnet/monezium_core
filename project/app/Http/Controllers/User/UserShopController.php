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
use App\Models\Campaign;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Charge;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;

class UserShopController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['products'] = Product::where('user_id','!=',auth()->id())->wherestatus(1)->get();
        $data['campaigns'] = Campaign::where('user_id','!=',auth()->id())->wherestatus(1)->get();
        return view('user.shop.index', $data);
    }

    public function order($id) {
        $data = Product::where('id', $id)->first();
        $bankaccounts = BankAccount::where('user_id', $data->user_id)->where('currency_id', $data->currency_id)->get();
        $cryptolist = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.shop.buy', compact('data', 'bankaccounts', 'cryptolist'));
    }

    public function donate($id) {
        $data = Campaign::where('id', $id)->first();
        $bankaccounts = BankAccount::where('user_id', auth()->id())->where('currency_id', $data->currency_id)->get();
        return view('user.shop.donate', compact('data', 'bankaccounts'));
    }

}

