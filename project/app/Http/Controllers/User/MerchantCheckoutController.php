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
use Illuminate\Support\Carbon as Carbontime;
use GuzzleHttp\Client;
use Datatables;

class MerchantCheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['link', 'link_pay', 'transaction']]);
    }

    public function index(){
        $data['checkouts'] = MerchantCheckout::where('user_id',auth()->id())->get();
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->whereStatus(1)->get();
        if (isEnabledUserModule('Crypto'))
            $data['currencylist'] = Currency::whereStatus(1)->get();
        else
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        return view('user.merchant.checkout.index', $data);
    }


    public function store(Request $request){
        $data = new MerchantCheckout();
        $input = $request->all();
        $input['ref_id'] = 'MC-'.Str::random(6);
        // $input['currency_id'] = MerchantWallet::where('merchant_id', $request->user_id)->where('shop_id', $request->shop_id)->where('currency_id', $request->currency_id)->first()->id;
        $data->fill($input)->save();
        return back()->with('message','Merchant Checkout has been created successfully');
    }

    Public function link($id) {
        $data['checkout'] = MerchantCheckout::where('ref_id', $id)->first();
        $crypto_ids =  MerchantWallet::where('merchant_id', $data['checkout']->user_id)->where('shop_id', $data['checkout']->shop_id)->pluck('currency_id')->toArray();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();
        return view('user.merchant.checkout.link', $data);
    }

    public function link_pay(Request $request, $id) {
        $data['checkout'] = MerchantCheckout::where('ref_id', $id)->first();
        $pre_currency = Currency::findOrFail($data['checkout']->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
        $data['merchantwallet'] =  MerchantWallet::where('merchant_id', $data['checkout']->user_id)->where('shop_id', $data['checkout']->shop_id)->where('currency_id', $select_currency->id)->first();
        return view('user.merchant.checkout.link_pay', $data);
    }

    public function transaction(Request $request) {
        $check = MerchantCheckout::whereId($request->id)->first();
        $user = User::findOrFail($check->user_id);

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $check->user_id;
        $trans->user_type   = 1;
        $trans->currency_id = $request->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'merchant_checkout';
        $trans->details     = trans('Merchant Checkout');
        $trans->data        = '{"hash":"'.$request->hash.'","status":"Pending","shop":"'.$check->shop->name.'", "receiver":"'.$user->name.'"}';
        $trans->save();
        return back()->with('success', 'You have done successfully');
    }

    public function transactionhistory() {
        // $history = Transaction::where('user_id', auth()->id())->where(re)
        $user = auth()->user();
        $search = request('search');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $transactions = Transaction::where('user_id',auth()->id())
        ->where('remark', 'merchant_checkout')
        ->when($search,function($q) use($search){
            return $q->where('trnx','LIKE',"%{$search}%");
        })
        ->whereBetween('created_at', [$s_time, $e_time])
        ->with('currency')->latest()->paginate(20);
        return view('user.merchant.checkout.transaction',compact('user','transactions', 'search',  's_time', 'e_time'));
    }

    public function edit($id) {
        $data['data'] = MerchantCheckout::findOrFail($id);
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->get();
        if (isEnabledUserModule('Crypto'))
            $data['currencylist'] = Currency::whereStatus(1)->get();
        else
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        return view('user.merchant.checkout.edit', $data);
    }

    public function update(Request $request, $id) {

        $data = MerchantCheckout::findOrFail($id);

        $input = $request->all();
        // $input['currency_id'] = MerchantWallet::where('merchant_id', $request->user_id)->where('shop_id', $request->shop_id)->where('currency_id', $request->currency_id)->first()->id;
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

    public function transaction_status($id, $status) {

        $data = Transaction::findOrFail($id);
        $tran_status = json_decode($data->data,true);
        
        if($tran_status['status'] == 'Completed') {
            return redirect()->route('user.merchant.checkout.transactionhistory')->with('warning','Merchant Checkout transaction status already is completed');
        }
        elseif($tran_status['status'] == 'Rejected') {
            return redirect()->route('user.merchant.checkout.transactionhistory')->with('warning','Merchant Checkout transaction status already is rejected');
        }
        else {
            $tran_status['status'] = $status;
            if ($status == 'Completed') {
                $shop = MerchantShop::where('name',$tran_status['shop'])->first();
                // dd($shop);
                $cryptowallet = MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $shop->id)->where('currency_id', $data->currency_id)->first();
                $cryptowallet->balance += $data->amount;
                $cryptowallet->save();
            }
        }
        $data->data = json_encode($tran_status);
        $data->update();

        return redirect()->route('user.merchant.checkout.transactionhistory')->with('message','Merchant Checkout transaction status has been changed successfully');
    }

    public function delete($id) {

        $data = MerchantCheckout::findOrFail($id);
        $data->delete();

        return redirect()->route('user.merchant.checkout.index')->with('message','Merchant Checkout status has been deleted successfully');
    }

    public function send_email(Request $request)
    {
        $to = $request->email;
        $subject = "Checkout";
        $msg = "Please check <a href='".$request->link."'>this link</a>";
        $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        mail($to,$subject,$msg,$headers);
        return back()->with('message', 'Email is sent successfully.');
    }
}

