<?php

namespace App\Http\Controllers\API;


use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Models\Currency;
use App\Models\CampaignCategory;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\Wallet;
use App\Models\SubInsBank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\CryptoDeposit;
use App\Models\DepositBank;
use App\Models\MerchantWallet;
use App\Models\Order;
use App\Models\Transaction as ModelsTransaction;
use GuzzleHttp\Client;

use Auth;

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
        try {
            $data['campaigns'] = Campaign::where('user_id',auth()->id())->get();
            $data['categories'] = CampaignCategory::where('user_id', auth()->id())->get();
            if (isEnabledUserModule('Crypto'))
                $data['currencies'] = Currency::whereStatus(1)->get();
            else
            $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function store(Request $request){
        try {
            $rules = [
                'logo' => 'required|mimes:jpg,git,png'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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
            $currency =  Currency::findOrFail($request->currency_id);
            mailSend('campaign_create',['campaign_title'=>$request->title, 'amount' => $request->goal, 'curr' => $currency->code], auth()->user());
            send_notification(auth()->id(), 'New Campaign has been created by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Campaign Title is ".$request->title."\n Please check.", route('admin.campaign.index'));
            send_staff_telegram('New Campaign has been created by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Campaign Title is ".$request->title."\n Please check.\n".route('admin.campaign.index'), 'Campaign');

             return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'New Campaign has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function edit($id) {
        try {
            $data['data'] = Campaign::findOrFail($id);
            $data['categories'] = CampaignCategory::where('user_id', auth()->id())->get();
            if (isEnabledUserModule('Crypto'))
                $data['currencies'] = Currency::whereStatus(1)->get();
            else
            $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        try {
            $rules = [
                'logo' => 'mimes:jpg,git,png'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Campaign has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function delete($id) {
        try {
            $data = Campaign::findOrFail($id);
            File::delete('assets/images/'.$data->logo);
            $data->delete();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Campaign has been deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function status($id) {
        try {
            $data = Campaign::findOrFail($id);
            $data->status = $data->status == 1 ? 0 : 1;
            $data->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Campaign status has been updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function category_create(Request $request){
        try {
            $data = New CampaignCategory();
            $data->user_id = $request->user_id;
            $data->name = $request->name;
            $data->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have created new category successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function pay(Request $request)
    {
        try {
            $data = Campaign::where('id', $request->campaign_id)->first();
            $totalamount = CampaignDonation::where('campaign_id', $request->campaign_id)->whereStatus(1)->sum('amount');

            if(!$data) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This campaign does not exist.']);
            }
            if($data->status == 0) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This compaign\'s status is deactive']);
            }
            $now = Carbon::now();
            if($now->gt($data->deadline)) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This compaign\'s deadline is passed']);
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

                $currency = Currency::findOrFail($data->currency_id);
                $user = User::findOrFail($data->user_id);
                mailSend('donate',['campaign_title'=>$data->title, 'amount' => $newdonation->amount, 'curr' => $currency->code, 'date_time'=>$newdonation->created_at, 'user_name' => $newdonation->user_name], $user);

                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.", route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.\n".route('admin.donation.index'), 'Donation');
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have donated for Campaign successfully (Payment Gateway).']);
            }
            elseif($request->payment == 'wallet'){
                if(!auth()->user()) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to login for this payment.']);
                }

                $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();

                $gs = Generalsetting::first();
                if(!$wallet){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have no '.$data->currency->code.' current wallet to pay for this.']);
                }

                if($wallet->balance < $request->amount) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your wallet']);
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
                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $campaign_user);
                    send_notification($campaign_user->id, 'New Current Wallet Created for '.($campaign_user->company_name ?? $campaign_user->name)."\n. Create Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $campaign_user->id));


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
                $currency = Currency::findOrFail($data->currency_id);
                $user = User::findOrFail($data->user_id);
                mailSend('donate',['campaign_title'=>$data->title, 'amount' => $newdonation->amount, 'curr' => $currency->code, 'date_time'=>$newdonation->created_at, 'user_name' => $newdonation->user_name], $user);

                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.", route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.\n".route('admin.donation.index'), 'Donation');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have donated for Campaign successfully.']);
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

                send_notification($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no, route('admin.deposits.bank.index'));
                send_whatsapp($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

                $newdonation = new CampaignDonation();
                $input = $request->all();
                $input['currency_id'] = $data->currency_id;
                $input['payment'] = 'bank_pay-'.$request->deposit_no;
                $newdonation->fill($input)->save();
                $currency = Currency::findOrFail($data->currency_id);
                $user = User::findOrFail($data->user_id);
                mailSend('donate',['campaign_title'=>$data->title, 'amount' => $newdonation->amount, 'curr' => $currency->code, 'date_time'=>$newdonation->created_at, 'user_name' => $newdonation->user_name], $user);

                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.", route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.\n".route('admin.donation.index'), 'Donation');
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have donated for Campaign successfully (Deposit Bank).']);

            }
            elseif($request->payment == 'crypto') {
                if(auth()->user()) {
                    if($request->amount > Crypto_Balance(auth()->id(), $request->currency_id)){
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient Balance.']);
                    }
                    $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$request->currency_id)->where('wallet_type', 8)->first();
                    $trans_wallet = get_wallet($data->user_id, $request->currency_id, 8);
                    
                    try {
                        $trnx = Crypto_Transfer($wallet, $trans_wallet->wallet_no, $request->amount);
                    } catch (\Throwable $th) {
                        return response()->json(['status' => '401', 'error_code' => '0', 'message' => __('You can not transfer money because Crypto have some issue: ') . $th->getMessage()]);
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
                $currency = Currency::findOrFail($data->currency_id);
                $user = User::findOrFail($data->user_id);
                mailSend('donate',['campaign_title'=>$data->title, 'amount' => $newdonation->amount, 'curr' => $currency->code, 'date_time'=>$newdonation->created_at, 'user_name' => $newdonation->user_name], $user);
                send_notification($data->user_id, 'Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.", route('admin.donation.index'));
                send_staff_telegram('Campaign has been donated by '.$request->user_name."\n Campaign Title is ".$data->title."\n Donate Amount : ".$currency->symbol.$newdonation->amount."\n Please check.\n".route('admin.donation.index'), 'Donation');
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have donated for Campaign successfully (Crypto).']);

            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function crypto($id)
    {
        try {
            $data['campaign'] = Campaign::where('id', $id)->first();
            $data['cryptolist'] = Currency::whereStatus(1)->where('type', 2)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function crypto_pay(Request $request, $id) {
        try {
            $data['campaign'] = Campaign::where('id', $id)->first();
            $data['total_amount'] = $request->amount;
            $data['description'] = $request->description;
            $pre_currency = Currency::findOrFail($data['campaign']->currency_id);
            $select_currency = Currency::findOrFail($request->link_pay_submit);
            $code = $select_currency->code;
            $data['cal_amount'] = floatval(getRate($pre_currency, $code));
            $data['wallet'] =  Wallet::where('user_id', $data['campaign']->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();
            if(!$data['wallet']) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $select_currency->code .' crypto wallet is not existed in Campaign Owner.']);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function donation_by_campaign($id)
    {
        try {
            $data['donations'] = CampaignDonation::where('campaign_id', $id)->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function send_email(Request $request)
    {
        try {
            $to = $request->email;
            $subject = "Campaign";
            $msg = "Please check <a href='".$request->link."'>this link</a>";
            $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            sendMail($to,$subject,$msg,$headers);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Email is sent successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
}

