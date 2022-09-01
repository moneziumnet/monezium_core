<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\MerchantShop;
use App\Models\MerchantCheckout;
use App\Models\MerchantWallet;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;

class MerchantCheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['link', 'link_pay', 'transaction']]);
    }

    public function index(){
        $data['checkouts'] = MerchantCheckout::where('user_id',auth()->id())->get();
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->get();
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        return view('user.merchant.checkout.index', $data);
    }

    public function create(){
        return view('user.merchant.shop.create');
    }

    public function store(Request $request){
        $data = new MerchantCheckout();
        $input = $request->all();
        $input['ref_id'] = 'MC-'.Str::random(6);
        $data->fill($input)->save();
        return back()->with('message','Merchant Checkout has been created successfully');
    }

    Public function link($id) {
        $data['checkout'] = MerchantCheckout::where('ref_id', $id)->first();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.merchant.checkout.link', $data);
    }

    public function link_pay($id) {
        $data['checkout'] = MerchantCheckout::where('ref_id', $id)->first();
        return view('user.merchant.checkout.link_pay', $data);
    }

    public function transaction(Request $request) {
        $check = MerchantCheckout::whereId($request->id)->first();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $check->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = $check->currency_id;
        $trans->amount      = $check->amount;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'merchant_checkout';
        $trans->details     = trans('Merchant Checkout');
        $trans->data        = '{"hash":"'.$request->hash.'"}';
        $trans->save();
    return back()->with('success', 'You have done successfully');
    }

    public function edit($id) {
        $data['data'] = MerchantCheckout::findOrFail($id);
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->get();
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        return view('user.merchant.checkout.edit', $data);
    }

    public function update(Request $request, $id) {

        $data = MerchantCheckout::findOrFail($id);

        $input = $request->all();
        $data->fill($input)->update();

        return redirect()->route('user.merchant.checkout.index')->with('message','Merchant Checkout has been updated successfully');
    }

    public function status($id) {

        $data = MerchantCheckout::findOrFail($id);
        if($data->status == 1) {
            $data->status = 0;
        }
        else {
            $data->status = 1;
        }
        $data->update();

        return redirect()->route('user.merchant.checkout.index')->with('message','Merchant Checkout status has been changed successfully');
    }

    public function delete($id) {

        $data = MerchantCheckout::findOrFail($id);
        $data->delete();

        return redirect()->route('user.merchant.checkout.index')->with('message','Merchant Checkout status has been deleted successfully');
    }
    // public function delete($id) {
    //     $data = MerchantShop::findOrFail($id);
    //     $data->delete();
    //     File::delete('assets/doc/'.$data->document);
    //     $wallets = MerchantWallet::where('shop_id', $id)->get();
    //     foreach ($wallets as $wallet) {
    //         $wallet->delete();
    //     }
    //     return  redirect()->back()->with('message','Merchant Shop has been deleted successfully');
    // }
}

