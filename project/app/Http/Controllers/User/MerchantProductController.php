<?php

namespace App\Http\Controllers\User;


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
use App\Models\SubInsBank;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Datatables;
use Illuminate\Support\Facades\Auth;

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
        if (!isEnabledUserModule('Crypto'))
            $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
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
        $currency =  Currency::findOrFail($request->currency_id);
        mailSend('merchant_product_created',['product_name'=>$request->name, 'amount' => $request->amount, 'curr' => $currency->code], auth()->user());
        send_notification(auth()->id(), 'New Merchant Product for '.(auth()->user()->company_name ?? auth()->user()->name).' is created.'."\n Product Name : ".$request->name."\n Product Price : ".$request->amount.$currency->code, route('admin.merchant.shop.index', auth()->id()));
        return redirect()->back()->with('message','New Product has been created successfully');
    }

    public function edit($id) {
        $data['data'] = Product::findOrFail($id);
        $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->whereStatus(1)->get();
        $data['categories'] = ProductCategory::where('user_id', auth()->id())->get();
        $data['currencies'] = Currency::whereStatus(1)->get();
        if (!isEnabledUserModule('Crypto'))
            $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
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
        $crypto_ids =  MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $data->shop_id)->pluck('currency_id')->toArray();
        $cryptolist = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();
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
        $data['quantity'] = $request->quantity;

        $data['name'] = $request->user_name;
        $data['address'] = $request->user_address;
        $data['phone'] = $request->user_phone;
        $data['email'] = $request->user_email;

        $data['total_amount'] = $data['product']->amount * $request->quantity;
        $pre_currency = Currency::findOrFail($data['product']->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
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

            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            $order = new Order();
            $order->product_id = $request->product_id;
            $order->user_id = $data->user_id;
            $order->shop_id = $data->shop_id;
            if(Auth::check()){
                $order->name = auth()->user()->company_name ?? auth()->user()->name;
                $order->email = auth()->user()->email;
                $order->phone = auth()->user()->phone;
                $order->address = auth()->user()->company_address ?? auth()->user()->address;
            } else {
                $order->name = $request->user_name;
                $order->email = $request->user_email;
                $order->phone = $request->user_phone;
                $order->address = $request->user_address;
            }
            $order->quantity = $request->quantity;
            $order->type = "Payment with Bank";
            $order->amount = $data->amount * $request->quantity;
            $order->save();
            $currency = Currency::where('id',$data->currency_id)->first();

            $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
            mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);

            send_notification($data->user_id, 'Bank has been deposited by '.$order->name."\n Amount is ".$currency->symbol.$order->amount."\n Transaction ID : ".$request->deposit_no, route('admin.deposits.bank.index'));
            send_whatsapp($data->user_id, 'Bank has been deposited by '.$order->name."\n Amount is ".$currency->symbol.$order->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_telegram($data->user_id, 'Bank has been deposited by '.$order->name."\n Amount is ".$currency->symbol.$order->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_staff_telegram('Bank has been deposited by '.$order->name."\n Amount is ".$currency->symbol.$order->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

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

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = 0;
                $trans_wallet = get_wallet($user->id, defaultCurr());
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = $chargefee->data->fixed_charge;
                $trans->type        = '-';
                $trans->remark      = 'account-open';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();

                $currency = Currency::findOrFail(defaultCurr());
                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $user);
                send_notification($user->id, 'New Current Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
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
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
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
            $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
            $rcvTrnx->save();

            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            $order = new Order();
            $order->product_id = $request->product_id;
            $order->user_id = $data->user_id;
            $order->shop_id = $data->shop_id;
            $order->name = auth()->user()->company_name ?? auth()->user()->name;
            $order->email = auth()->user()->email;
            $order->phone = auth()->user()->phone;
            $order->address = auth()->user()->company_address ?? auth()->user()->address;
            $order->quantity = $request->quantity;
            $order->type = "Payment with Account";
            $order->amount = $data->amount * $request->quantity;
            $order->save();


            mailSend('merchant_product_selled',['amount'=>$data->amount * $request->quantity, 'product_name'=> $data->name, 'product_amount' => $data->amount, 'quantity' => $request->quantity ,'date_time'=>$order->created_at, 'type' => $order->type, 'buyer'=>$order->name, 'trnx' => $rcvTrnx->trnx ], $data->user);
            send_notification($data->user_id, 'Merchant Product for '.($data->user->company_name ?? $data->user->name).' is selled.'."\n Product Name : ".$data->name."\n Transaction ID : ".$rcvTrnx->trnx, route('admin-user-transactions', $data->user_id));
            return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully ');

        }
        elseif($request->payment == 'crypto') {
            $crytpo_data = new CryptoDeposit();
            $crytpo_data->currency_id = $request->currency_id;
            $crytpo_data->amount = $request->amount;
            $crytpo_data->user_id = $data->user_id;
            $crytpo_data->address = $request->address;
            $crytpo_data->save();


            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            $order = new Order();
            $order->product_id = $request->product_id;
            $order->user_id = $data->user_id;
            $order->shop_id = $data->shop_id;
            if(Auth::check()){
                $order->name = auth()->user()->company_name ?? auth()->user()->name;
                $order->email = auth()->user()->email;
                $order->phone = auth()->user()->phone;
                $order->address = auth()->user()->company_address ?? auth()->user()->address;
            } else {
                $order->name = $request->user_name;
                $order->email = $request->user_email;
                $order->phone = $request->user_phone;
                $order->address = $request->user_address;
            }

            $order->quantity = $request->quantity;
            $order->type = "Payment with Crypto";
            $order->amount = $data->amount * $request->quantity;
            $order->save();

            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Crypto).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Crypto).');
            }
        }
        elseif($request->payment == 'gateway') {

            $data->quantity = $data->quantity - $request->quantity;
            $data->sold = $data->sold + $request->quantity;
            $data->update();

            $order = new Order();
            $order->product_id = $request->product_id;
            $order->user_id = $data->user_id;
            $order->shop_id = $data->shop_id;
            if(Auth::check()){
                $order->name = auth()->user()->company_name ?? auth()->user()->name;
                $order->email = auth()->user()->email;
                $order->phone = auth()->user()->phone;
                $order->address = auth()->user()->company_address ?? auth()->user()->address;
            } else {
                $order->name = $request->user_name;
                $order->email = $request->user_email;
                $order->phone = $request->user_phone;
                $order->address = $request->user_address;
            }
            $order->quantity = $request->quantity;
            $order->type = "Payment with Gateway";
            $order->amount = $data->amount * $request->quantity;
            $order->save();

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
        $data['quantity'] = $request->quantity;
        $data['total_amount'] = $data['product']->amount * $request->quantity;
        $pre_currency = Currency::findOrFail($data['product']->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
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
        $msg = "Please order <a href='".$request->link."'>this product</a> <br> Please check QR code: <img src='".generateQR($request->link)."' class='' alt=''>";
        $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        sendMail($to,$subject,$msg,$headers);
        return back()->with('message', 'Email is sent successfully.');
    }
}

