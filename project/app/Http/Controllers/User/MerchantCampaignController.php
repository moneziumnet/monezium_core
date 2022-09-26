<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Models\Currency;
use App\Models\CampaignCategory;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Datatables;
use Illuminate\Support\Carbon;
use App\Models\BankAccount;
use App\Models\MerchantWallet;
use GuzzleHttp\Client;

use PayPal\{
    Api\Item,
    Api\Payer,
    Api\Amount,
    Api\Payment,
    Api\ItemList,
    Rest\ApiContext,
    Api\Transaction,
    Api\RedirectUrls,
    Api\PaymentExecution,
    Auth\OAuthTokenCredential,
    Api\Payout,
    Api\PayoutSenderBatchHeader,
    Api\PayoutItem,
    Api\Currency As PaypalCurrency,
};

class MerchantCampaignController extends Controller
{
    private $_api_context;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['link', 'crypto_link', 'crypto_link_pay', 'pay']]);
        $data = PaymentGateway::whereKeyword('paypal')->first();
        $paydata = $data->convertAutoData();

        $paypal_conf = \Config::get('paypal');
        $paypal_conf['client_id'] = $paydata['client_id'];
        $paypal_conf['secret'] = $paydata['client_secret'];
        $paypal_conf['settings']['mode'] = $paydata['sandbox_check'] == 1 ? 'sandbox' : 'live';
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function index(){
        $data['campaigns'] = Campaign::where('user_id',auth()->id())->get();
        $data['categories'] = CampaignCategory::where('user_id', auth()->id())->get();
        $data['currencies'] = Currency::whereStatus(1)->get();
        return view('user.merchant.campaign.index', $data);
    }

    public function store(Request $request){
        $rules = [
            'logo' => 'required|mimes:jpg,git,png'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['logo'][0]);
        }


        $data = new Campaign();
        if ($file = $request->file('logo'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
        }
        $input = $request->all();
        $input['ref_id'] ='CP-'.Str::random(6);
        $input['logo'] = $name;
        $data->fill($input)->save();
         return redirect()->back()->with('message','New Campaign has been created successfully');
    }

    public function edit($id) {
        $data['data'] = Campaign::findOrFail($id);
        $data['categories'] = CampaignCategory::where('user_id', auth()->id())->get();
        $data['currencies'] = Currency::whereStatus(1)->get();
        return view('user.merchant.campaign.edit', $data);
    }

    public function update(Request $request, $id) {
        $rules = [
            'logo' => 'mimes:jpg,git,png'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['logo'][0]);
        }

        $data = Campaign::findOrFail($id);
        $input = $request->all();
        if ($file = $request->file('logo'))
        {
            File::delete('assets/images/'.$data->logo);
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            $input['logo'] = $name;
        }
        $data->fill($input)->update();

        return redirect()->route('user.merchant.campaign.index')->with('message','Campaign has been updated successfully');
    }

    public function delete($id) {
        $data = Campaign::findOrFail($id);
        File::delete('assets/images/'.$data->logo);
        $data->delete();
        return  redirect()->back()->with('message','Campaign has been deleted successfully');
    }

    public function status($id) {
        $data = Campaign::findOrFail($id);
        $data->status = $data->status == 1 ? 0 : 1;
        $data->update();
        return back()->with('message', 'Campaign status has been updated successfully.');
    }

    public function category_create(Request $request){
        $data = New CampaignCategory();
        $data->user_id = $request->user_id;
        $data->name = $request->name;
        $data->save();
        return back()->with('message', 'You have created new category successfully.');
    }

    public function link($ref_id) {
        $data = Campaign::where('ref_id', $ref_id)->first();
        $bankaccounts = BankAccount::where('user_id', $data->user_id)->where('currency_id', $data->currency_id)->get();
        $cryptolist= Currency::whereStatus(1)->where('type', 2)->get();
        if(!$data) {
            return back()->with('error', 'This Campaign does not exist.');
        }
        if($data->status == 0) {
            return back()->with('error', 'This Campaign\'s status is deactive');
        }
        return view('user.merchant.campaign.pay', compact('data', 'bankaccounts', 'cryptolist'));
    }

    public function pay(Request $request)
    {
        $data = Campaign::where('id', $request->campaign_id)->first();
        $totalamount = CampaignDonation::where('campaign_id', $request->campaign_id)->whereStatus(1)->sum('amount');

        if(!$data) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'This campaign does not exist.');
            }
            else {
                return redirect(url('/'))->with('error', 'This campaign does not exist.');
            }
        }
        if($data->status == 0) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'This compaign\'s status is deactive');
            }
            else {
                return redirect(url('/'))->with('error', 'This compaign\'s status is deactive');
            }
        }
        $now = Carbon::now();
        if($now->gt($data->deadline)) {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('error', 'This compaign\'s deadline is passed');
            }
            else {
                return redirect(url('/'))->with('error', 'This compaign\'s deadline is passed');
            }
        }
        if($request->payment == 'gateway'){
            // $settings = Generalsetting::findOrFail(1);

            // $payouts = new Payout();
            // $senderBatchHeader = new PayoutSenderBatchHeader();

            // $senderBatchHeader->setSenderBatchId(Str::random(12))
            //                 ->setEmailSubject('You have a Payout');

            // $senderItem = new PayoutItem();
            // $senderItem->setRecipientType('Email')
            //         ->setNote('This is for Campaign.')
            //         ->setSenderItemId(Str::random(12))
            //         ->setReceiver('appc31058@gmail.com')
            //         ->setAmount(new PaypalCurrency('{
            //             "value":"'.$data->amount.'",
            //             "currency":"'.$data->currency->code.'"
            //         }'));
            // $payouts->setSenderBatchHeader($senderBatchHeader)
            //         ->addItem($senderItem);

            // $sender_request = clone $payouts;

            // try {
            //     $output = $payouts->create(null, $this->_api_context);
            // } catch (Throwable $ex) {
            //     return redirect(route('user.dashboard'))->with('error', $th->getMessage());
            // }
            // $newdonation = new CampaignDonation();
            // $input = $request->all();
            // $input['currency_id'] = $data->currency_id;
            // $newdonation->fill($input)->save();
            // return redirect(route('user.dashboard'))->with('message','You have donated for Campaign successfully.');
        }
        elseif($request->payment == 'wallet'){
            if(!auth()->user()) {
                return redirect(route('user.login'))->with('error', 'You have to login for this payment.');
            }
            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();

            if($wallet->balance < $data->amount) {
                return redirect(route('user.dashboard'))->with('error','Insufficient balance to your wallet');
            }


            $newdonation = new CampaignDonation();
            $input = $request->all();
            $input['currency_id'] = $data->currency_id;
            $newdonation->fill($input)->save();

            $gs = Generalsetting::first();

            return redirect(route('user.dashboard'))->with('message','You have donated for Campaign successfully.');
        }
        elseif($request->payment == 'bank_pay'){
            // $bankaccount = BankAccount::where('id', $request->bank_account)->first();
            // $currency = Currency::where('id',$data->currency_id)->first();
            // $user = User::findOrFail($bankaccount->user_id);

            // $trans = new Transaction();
            // $trans->trnx = str_rand();
            // $trans->user_id     = $user->user_id;
            // $trans->user_type   = 1;
            // $trans->currency_id = $currency->currency_id;
            // $trans->amount      = $data->amount * $request->quantity;
            // $trans->charge      = 0;
            // $trans->type        = '+';
            // $trans->remark      = 'merchant_product_buy';
            // $trans->details     = trans('Merchant Product Buy by Bank');
            // $trans->data        = '{"Bank":"'.$bankaccount->subbank->name.'","status":"Pending", "receiver":"'.$user->name.'"}';
            // $trans->save();

            // $data->quantity = $data->quantity - $request->quantity;
            // $data->sold = $data->sold + $request->quantity;
            // $data->update();

            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Deposit Bank).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Deposit Bank).');
            }
            // return 'bank';
        }
        elseif($request->payment = 'crypto') {
            if(auth()->user()) {
                return redirect(route('user.shop.index'))->with('message','You have paid for buy project successfully (Crypto).');
            }
            else {
                return redirect(url('/'))->with('message','You have paid for buy project successfully (Crypto).');
            }
        }
    }

    public function crypto($id)
    {
        $data['campaign'] = Campaign::where('id', $id)->first();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.merchant.campaign.crypto', $data);
    }

    public function crypto_pay(Request $request, $id) {
        $data['campaign'] = Campaign::where('id', $id)->first();
        $data['total_amount'] = $request->amount;
        $pre_currency = Currency::findOrFail($data['campaign']->currency_id)->code;
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$code);
        $result = json_decode($response->getBody());
        $data['cal_amount'] = floatval($result->data->rates->$pre_currency);
        $data['wallet'] =  Wallet::where('user_id', $data['campaign']->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();
        if(!$data['wallet']) {
            return back()->with('error', $select_currency->code .' crypto wallet is not existed in Campaign Owner.');
        }
        return view('user.merchant.campaign.crypto_pay', $data);
    }

    public function crypto_link($id)
    {
        $data['campaign'] = Campaign::where('id', $id)->first();
        $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
        return view('user.merchant.campaign.crypto_link', $data);
    }

    public function crypto_link_pay(Request $request, $id) {
        $data['campaign'] = Campaign::where('id', $id)->first();
        $data['total_amount'] = $request->amount ;
        $pre_currency = Currency::findOrFail($data['campaign']->currency_id)->code;
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $client = New Client();
        $code = $select_currency->code;
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency='.$code);
        $result = json_decode($response->getBody());
        $data['cal_amount'] = floatval($result->data->rates->$pre_currency);
        $data['wallet'] =  Wallet::where('user_id', $data['campaign']->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();
        if(!$data['wallet']) {
            return back()->with('unsuccess', $select_currency->code .' crypto wallet is not existed in Campaign Owner.');
        }
        return view('user.merchant.campaign.crypto_link_pay', $data);
    }

    public function donation_by_campaign($id)
    {
        $data['donations'] = CampaignDonation::where('campaign_id', $id)->latest()->paginate(15);
        return view('user.merchant.campaign.donation', $data);
    }

    public function send_email(Request $request)
    {
        $to = $request->email;
        $subject = "Campaign";
        $msg = "Please check <a href='".$request->link."'>this link</a>";
        $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        mail($to,$subject,$msg,$headers);
        return back()->with('message', 'Email is sent successfully.');

    }
}

