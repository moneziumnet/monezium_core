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
use App\Models\DepositBank;
use App\Models\CryptoDeposit;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Datatables;

class MerchantProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['link', 'crypto_link', 'crypto_link_pay', 'pay']]);
    }

    public function index(){
        $data['products'] = Product::where('user_id',auth()->id())->get();
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

    public function status($id) {
        $data = Product::findOrFail($id);
        $data->status = $data->status == 1 ? 0 : 1;
        $data->update();
        return back()->with('message', 'Merchant Product status has been updated successfully.');
    }

    public function category_create(Request $request){
        $data = New ProductCategory();
        $data->user_id = $request->user_id;
        $data->name = $request->name;
        $data->save();
        return back()->with('message', 'You have created new category successfully.');
    }

    public function link($ref_id) {
        $data = Product::where('ref_id', $ref_id)->first();
        $bankaccounts = BankAccount::where('user_id', $data->user_id)->where('currency_id', $data->currency_id)->get();
        $cryptolist = Currency::whereStatus(1)->where('type', 2)->get();
        if(!$data) {
            return back()->with('error', 'This product does not exist.');
        }
        if($data->status == 0) {
            return back()->with('error', 'This product\'s sell status is deactive');
        }
        return view('user.merchant.product.product_pay', compact('data', 'bankaccounts', 'cryptolist'));
    }

    public function crypto_link($id)
    {
        $data['product'] = Product::where('id', $id)->first();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.merchant.product.crypto_link', $data);
    }

    public function crypto_link_pay(Request $request, $id) {
        $data['product'] = Product::where('id', $id)->first();
        $data['total_amount'] = $data['product']->amount * $request->quantity;
        $pre_currency = Currency::findOrFail($data['product']->currency_id)->code;
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$code);
        $result = json_decode($response->getBody());
        $data['cal_amount'] = floatval($result->data->rates->$pre_currency);
        $data['merchantwallet'] =  MerchantWallet::where('merchant_id', $data['product']->user_id)->where('shop_id', $data['product']->shop_id)->where('currency_id', $select_currency->id)->first();
        return view('user.merchant.product.crypto_link_pay', $data);
    }

    public function pay(Request $request)
    {
        $data = Product::where('id', $request->product_id)->first();
        
        if(!$data) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'This product does not exist.');
            }
            else {
                return redirect(url('/'))->with('error', 'This product does not exist.');
            }
        }
        if($data->status == 0) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'This product\'s sell status is deactive');
            }
            else {
                return redirect(url('/'))->with('error', 'This product\'s sell status is deactive');
            }
        }
        if($data->quantity < $request->quantity) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'The product\'s quantity is smaller than your quantity');
            }
            else {
                return redirect(url('/'))->with('error', 'The product\'s quantity is smaller than your quantity');
            }
        }
        if($data->user_id == auth()->id()) {
            return redirect(route('user.dashboard'))->with('error', 'You can not buy your product.');
        }
        
        if($request->payment == 'bank_pay'){
            
            $bankaccount = BankAccount::where('id', $request->bank_account)->first();
            $currency = Currency::where('id',$data->currency_id)->first();
            $user = User::findOrFail($bankaccount->user_id);
            $deposit = new DepositBank();
            $deposit['deposit_number'] = $request->deposit_no;
            $deposit['user_id'] = $data->user_id;
            $deposit['currency_id'] = $data->currency_id;
            $deposit['amount'] = $request->quantity * $data->amount;
            $deposit['sub_bank_id'] = $bankaccount->subbank_id;
            $deposit['details'] = $request->description;
            $deposit['status'] = "pending";
            $deposit->save();

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency->id;
            $trans->amount      = $data->amount * $request->quantity;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'merchant_product_buy';
            $trans->details     = trans('Merchant Product Buy by Bank');
            $trans->data        = '{"Bank":"'.$bankaccount->subbank->name.'","status":"Pending", "receiver":"'.$user->name.'"}';
            $trans->save();

            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Deposit Bank).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Deposit Bank).');
            }
            // return 'bank';
        }
        elseif($request->payment == 'wallet'){
            if(!auth()->user()) {
                return redirect(route('user.login'))->with('error', 'You have to login for this payment.');
            }
            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();

            $gs = Generalsetting::first();
            if(!$wallet){
                $wallet =  Wallet::create([
                    'user_id'     => auth()->id(),
                    'user_type'   => 1,
                    'currency_id' => $data->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail(auth()->id());

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans_wallet = get_wallet($user->id, 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }

            if($wallet->balance < $data->amount * $request->quantity) {
                return redirect(route('user.dashboard'))->with('error','Insufficient balance to your wallet');
            }

            $wallet->balance -= $data->amount * $request->quantity;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $data->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $data->amount * $request->quantity;
            $trnx->charge      = 0;
            $trnx->remark      = 'merchant_product_buy';
            $trnx->type        = '-';
            $trnx->details     = trans('Payment to buy product : '). $data->ref_id;
            $trnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($data->user_id)->name.'"}';
            $trnx->save();

            $rcvWallet = MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $data->shop_id)->where('currency_id', $data->currency_id)->first();

            $rcvWallet->balance += $data->amount * $request->quantity;
            $rcvWallet->update();

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $data->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $data->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $data->amount * $request->quantity;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'product_sell_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Payment to sell product : '). $data->ref_id;
            $rcvTrnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($data->user_id)->name.'"}';
            $rcvTrnx->save();

            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            $order = new Order();
            $order->product_id = $request->product_id;
            $order->user_id = $data->user_id;
            $order->shop_id = $data->shop_id;
            $order->name = auth()->user()->name;
            $order->email = auth()->user()->email;
            $order->phone = auth()->user()->phone;
            $order->address = auth()->user()->address;
            $order->quantity = $request->quantity;
            $order->type = "Payment with Account";
            $order->amount = $data->amount * $request->quantity;
            $order->save();

            $to = $data->user->email;
            $subject = "Received product payments";
            $msg_body = "You received money ".amount($data->amount * $request->quantity,$data->currency->type,2)." \n The customers buy your products." ;
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            mail($to,$subject,$msg_body,$headers);

        }
        elseif($request->payment = 'crypto') {
            $crytpo_data = new CryptoDeposit();
            $crytpo_data->currency_id = $request->currency_id;
            $crytpo_data->amount = $request->amount;
            $crytpo_data->user_id = $data->user_id;
            $crytpo_data->address = $request->address;
            $crytpo_data->save();

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $data->user_id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency->currency_id;
            $trans->amount      = $request->amount;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = 'merchant_product_buy';
            $trans->details     = trans('Merchant Product Buy by Bank');
            $trans->data        = '{"Bank":"'.$bankaccount->subbank->name.'","status":"Pending", "receiver":"'.$data->user->name.'"}';
            $trans->save();
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Crypto).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Crypto).');
            }
        }
        elseif($request->payment = 'gateway') {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Payment Gateway).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Payment Gateway).');
            }
        }
        return redirect(route('user.shop.index'))->with('success','You have paid for buy project successfully.');
    }

    public function crypto($id)
    {
        $data['product'] = Product::where('id', $id)->first();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.merchant.product.crypto', $data);
    }

    public function crypto_pay(Request $request, $id) {
        $data['product'] = Product::where('id', $id)->first();
        $data['total_amount'] = $data['product']->amount * $request->quantity;
        $pre_currency = Currency::findOrFail($data['product']->currency_id)->code;
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$code);
        $result = json_decode($response->getBody());
        $data['cal_amount'] = floatval($result->data->rates->$pre_currency);
        $data['merchantwallet'] =  MerchantWallet::where('merchant_id', $data['product']->user_id)->where('shop_id', $data['product']->shop_id)->where('currency_id', $select_currency->id)->first();
        return view('user.merchant.product.crypto_pay', $data);
    }

    public function order()
    {
        $data['orders'] = Order::where('user_id', auth()->id())->paginate(15);
        return view('user.merchant.product.order', $data);
    }

    public function order_by_product($id)
    {
        $data['orders'] = Order::where('product_id', $id)->paginate(15);
        return view('user.merchant.product.order', $data);
    }

    public function send_email(Request $request)
    {
        $to = $request->email;
        $subject = "Order Product";
        $msg = "Please order <a href='".$request->link."'>this product</a> <br> PLease check QR code: <img src='".generateQR($request->link)."' class='' alt=''>";
        $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        mail($to,$subject,$msg,$headers);
        return back()->with('message', 'Email is sent successfully.');
    }
}

