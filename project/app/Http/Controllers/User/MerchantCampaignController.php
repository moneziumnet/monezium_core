<?php

namespace App\Http\Controllers\User;


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
use App\Models\Charge;
use App\Models\CryptoDeposit;
use App\Models\DepositBank;
use App\Models\MerchantWallet;
use App\Models\Order;
use App\Models\Transaction as ModelsTransaction;
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
        if (isEnabledUserModule('Crypto'))
            $data['currencies'] = Currency::whereStatus(1)->get();
        else
        $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();

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
        send_notification(auth()->id(), 'New Campaign has been created by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.campaign.index'));
        send_staff_telegram('New Campaign has been created by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.campaign.index'), 'Campaign');

         return redirect()->back()->with('message','New Campaign has been created successfully');
    }

    public function edit($id) {
        $data['data'] = Campaign::findOrFail($id);
        $data['categories'] = CampaignCategory::where('user_id', auth()->id())->get();
        if (isEnabledUserModule('Crypto'))
            $data['currencies'] = Currency::whereStatus(1)->get();
        else
        $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
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
        $crypto_ids =  Wallet::where('user_id', $data->user_id)->where('user_type',1)->where('wallet_type', 8)->pluck('currency_id')->toArray();
        $cryptolist= Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();
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
            $newdonation = new CampaignDonation();
            $input = $request->all();
            $input['currency_id'] = $data->currency_id;
            $input['status'] = 1;
            $newdonation->fill($input)->save();
            if(auth()->user()) {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(route('user.shop.index'))->with('message','You have donated for Campaign successfully (Payment Gateway).');
            }
            else {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(url('/'))->with('message','You have donated for Campaign successfully (Payment Gateway).');
            }
        }
        elseif($request->payment == 'wallet'){
            if(!auth()->user()) {
                return redirect(route('user.login'))->with('error', 'You have to login for this payment.');
            }

            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();

            $gs = Generalsetting::first();
            if(!$wallet){
                return redirect()->back()->with('error', 'You have no '.$data->currency->code.' current wallet to pay for this.');

            }

            if($wallet->balance < $request->amount) {
                return redirect()->back()->with('error','Insufficient balance to your wallet');
            }

            $wallet->balance -= $request->amount;
            $wallet->update();

            $trnx              = new ModelsTransaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $data->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $request->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'campaign_payment';
            $trnx->type        = '-';
            $trnx->details     = trans('Payment to campaign : '). $data->ref_id;
            $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name ).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
            $trnx->save();

            $rcvWallet = Wallet::where('user_id', $data->user_id)->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();
            if(!$rcvWallet){
                $rcvWallet =  Wallet::create([
                    'user_id'     => $data->user_id,
                    'user_type'   => 1,
                    'currency_id' => $data->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $campaign_user = User::findOrFail($data->user_id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $campaign_user->bank_plan_id)->where('user_id', $campaign_user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $campaign_user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new ModelsTransaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $campaign_user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = 0;
                $trans_wallet = get_wallet($campaign_user->id, defaultCurr());
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = $chargefee->data->fixed_charge;
                $trans->type        = '-';
                $trans->remark      = 'account-open';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$campaign_user->name.'", "receiver":"'.$gs->disqus.'"}';
                $trans->save();

                $currency = Currency::findOrFail(defaultCurr());
                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], auth()->user());


                user_wallet_decrement($campaign_user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            $rcvWallet->balance += $request->amount;
            $rcvWallet->update();

            $rcvTrnx              = new ModelsTransaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $data->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $data->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $request->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'campaign_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Campaign Payment : '). $data->ref_id;
            $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
            $rcvTrnx->save();

            $newdonation = new CampaignDonation();
            $input = $request->all();
            $input['currency_id'] = $data->currency_id;
            $input['status'] = 1;
            $newdonation->fill($input)->save();

            send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
            send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

            return redirect()->back()->with('message','You have donated for Campaign successfully.');
        }
        elseif($request->payment == 'bank_pay'){

            $bankaccount = BankAccount::where('id', $request->bank_account)->first();
            $deposit = new DepositBank();
            $deposit['deposit_number'] = $request->deposit_no;
            $deposit['user_id'] = $data->user_id;
            $deposit['currency_id'] = $data->currency_id;
            $deposit['amount'] = $request->amount;
            $deposit['sub_bank_id'] = $bankaccount->subbank_id;
            $deposit['details'] = $request->description;
            $deposit['status'] = "pending";
            $deposit->save();
            $currency = Currency::where('id',$data->currency_id)->first();

            $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
            $user = User::findOrFail($bankaccount->user_id);
            mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);

            send_notification($data->user_id, 'Bank has been deposited by '.$request->user_name.'. Please check.', route('admin.deposits.bank.index'));
            send_whatsapp($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_telegram($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
            send_staff_telegram('Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

            $newdonation = new CampaignDonation();
            $input = $request->all();
            $input['currency_id'] = $data->currency_id;
            $input['payment'] = 'bank_pay-'.$request->deposit_no;
            $newdonation->fill($input)->save();

            if(auth()->user()) {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(route('user.shop.index'))->with('message','You have donated for Campaign successfully (Deposit Bank).');
            }
            else {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(url('/'))->with('message','You have donated for Campaign successfully (Deposit Bank).');
            }

            // return 'bank';
        }
        elseif($request->payment == 'crypto') {
            if(auth()->user()) {
                if($request->amount > Crypto_Balance(auth()->id(), $request->currency_id)){
                    return redirect()->back()->with('unsuccess','Insufficient Balance.');
                }
                $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 8)->first();
                $trans_wallet = get_wallet($data->user_id, $request->currency_id, 8);
                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$trans_wallet->wallet_no.'", "value": "0x'.dechex($request->amount*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    $res = RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, amount($request->amount, 2)],$wallet->keyword);
                    if (isset($res->error->message)){
                        return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                    }
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $request->amount, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                    }
                }
                $trnx              = new ModelsTransaction();
                $trnx->trnx        = str_rand();
                $trnx->user_id     = auth()->id();
                $trnx->user_type   = 1;
                $trnx->currency_id = $request->currency_id;
                $trnx->wallet_id   = $wallet->id;
                $trnx->amount      = $request->amount;
                $trnx->charge      = 0;
                $trnx->remark      = 'campaign_payment';
                $trnx->type        = '-';
                $trnx->details     = trans('Payment to campaign : '). $data->ref_id;
                $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
                $trnx->save();

                $rcvTrnx              = new ModelsTransaction();
                $rcvTrnx->trnx        = $trnx->trnx;
                $rcvTrnx->user_id     = $data->user_id;
                $rcvTrnx->user_type   = 1;
                $rcvTrnx->currency_id = $request->currency_id;
                $rcvTrnx->wallet_id   = $trans_wallet->id;
                $rcvTrnx->amount      = $request->amount;
                $rcvTrnx->charge      = 0;
                $rcvTrnx->remark      = 'campaign_payment';
                $rcvTrnx->type        = '+';
                $rcvTrnx->details     = trans('Receive Campaign Payment : '). $data->ref_id;
                $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
                $rcvTrnx->save();

                $currency = Currency::findOrFail($request->currency_id);

                $newdonation = new CampaignDonation();
                $newdonation->campaign_id = $data->id;
                $newdonation->user_name = auth()->user()->company_name ?? auth()->user()->name;
                $newdonation ->currency_id = $data->currency_id;
                $newdonation->amount = $request->amount/getRate($currency);
                $newdonation->payment = 'crypto';
                $newdonation->description = $request->description;
                $newdonation->status = 1;
                $newdonation->save();
            }
            else{
                $currency = Currency::findOrFail($request->currency_id);
                $newdonation = new CampaignDonation();
                $newdonation->campaign_id = $data->id;
                $newdonation->user_name = $request->user_name;
                $newdonation ->currency_id = $request->currency_id;
                $newdonation->amount = $request->amount;
                $newdonation->payment = 'crypto';
                $newdonation->description = $request->description;
                $newdonation->status = 0;
                $newdonation->save();
            }

            if(auth()->user()) {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(route('user.shop.index'))->with('message','You have donated for Campaign successfully (Crypto).');
            }
            else {
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name.'. Please check.', route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name.". Please check.\n".route('admin.donation.index'), 'Donation');

                return redirect(url('/'))->with('message','You have donated for Campaign successfully (Crypto).');
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
        $data['description'] = $request->description;
        $pre_currency = Currency::findOrFail($data['campaign']->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
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
        $data['description'] = $request->description;
        $data['user_name'] = $request->user_name;
        $pre_currency = Currency::findOrFail($data['campaign']->currency_id);
        $select_currency = Currency::findOrFail($request->link_pay_submit);
        $code = $select_currency->code;
        $data['cal_amount'] = floatval(getRate($pre_currency, $code));
        $data['wallet'] =  Wallet::where('user_id', $data['campaign']->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();
        if(!$data['wallet']) {
            return back()->with('error', $select_currency->code .' crypto wallet is not existed in Campaign Owner.');
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
        sendMail($to,$subject,$msg,$headers);
        return back()->with('message', 'Email is sent successfully.');

    }
}

