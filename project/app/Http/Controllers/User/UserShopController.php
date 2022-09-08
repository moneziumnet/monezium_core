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
        return view('user.shop.index', $data);
    }

    public function buy($id) {
        $data = Product::where('id', $id)->first();
        return view('user.shop.buy', compact('data'));
    }

}

