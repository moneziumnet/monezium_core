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
        $this->middleware('auth', ['except' => ['link']]);
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
        if(!$data) {
            return back()->with('error', 'This Campaign does not exist.');
        }
        if($data->status == 0) {
            return back()->with('error', 'This Campaign\'s status is deactive');
        }
        return view('user.merchant.campaign.pay', compact('data'));
    }

    public function pay(Request $request)
    {
        $data = Campaign::where('id', $request->campaign_id)->first();
        $totalamount = CampaignDonation::where('campaign_id', $request->campaign_id)->whereStatus(1)->sum('amount');

        if(!$data) {
            return redirect(route('user.dashboard'))->with('error', 'This campaign does not exist.');
        }
        if($data->status == 0) {
            return redirect(route('user.dashboard'))->with('error', 'This compaign\'s status is deactive');
        }
        $now = Carbon::now();
        if($now->gt($data->deadline)) {
            return redirect(route('user.dashboard'))->with('error', 'This compaign\'s deadline is passed');
        }
        if($request->payment == 'gateway'){
            $settings = Generalsetting::findOrFail(1);

            $payouts = new Payout();
            $senderBatchHeader = new PayoutSenderBatchHeader();

            $senderBatchHeader->setSenderBatchId(Str::random(12))
                            ->setEmailSubject('You have a Payout');

            $senderItem = new PayoutItem();
            $senderItem->setRecipientType('Email')
                    ->setNote('This is for Campaign.')
                    ->setSenderItemId(Str::random(12))
                    ->setReceiver('appc31058@gmail.com')
                    ->setAmount(new PaypalCurrency('{
                        "value":"'.$data->amount.'",
                        "currency":"'.$data->currency->code.'"
                    }'));
            $payouts->setSenderBatchHeader($senderBatchHeader)
                    ->addItem($senderItem);

            $sender_request = clone $payouts;

            try {
                $output = $payouts->create(null, $this->_api_context);
            } catch (Throwable $ex) {
                return redirect(route('user.dashboard'))->with('error', $th->getMessage());
            }
            $newdonation = new CampaignDonation();
            $input = $request->all();
            $input['currency_id'] = $data->currency_id;
            $newdonation->fill($input)->save();
            return redirect(route('user.dashboard'))->with('message','You have donated for Campaign successfully.');
        }
        elseif($request->payment == 'wallet'){
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
        return back()->with('success', 'Email is sent successfully.');

    }
}

