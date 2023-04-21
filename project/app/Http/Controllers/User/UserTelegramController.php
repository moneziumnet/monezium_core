<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Admin;
use App\Models\UserTelegram;
use App\Models\UserWhatsapp;
use App\Models\Currency;
use App\Models\BotWebhook;
use App\Models\TelegramSession;
use App\Models\Beneficiary;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\BankPoolAccount;
use App\Models\BankPlan;
use App\Models\PlanDetail;
use App\Models\BalanceTransfer;
use App\Models\BankGateway;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\MoneyRequest;
use App\Models\CryptoWithdraw;
use App\Models\Charge;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Log;

class UserTelegramController extends Controller
{
    private $beneficiary_json =array(
        "type"=>"Please input first name.",
        "first_name"=>"Please input last name.",
        "last_name"=>"Please input email.",
        "email"=>"Please input address.",
        "address"=>"Please input phone.",
        "phone"=>"Please input Registration NO.",
        "registration_no"=>"Please input VAT NO.",
        "vat_no"=>"Please input contact person.",
        "contact_person"=>"Please input Bank IBAN.",
        "account_iban"=>"You completed beneficiary register successfully."
        );
        private $beneficiary_company_json = array(
        "type"=>"Please input Company name.",
        "company_name"=>"Please input email.",
        "email"=>"Please input address.",
        "address"=>"Please input phone.",
        "phone"=>"Please input Registration NO.",
        "registration_no"=>"Please input VAT NO.",
        "vat_no"=>"Please input contact person.",
        "contact_person"=>"Please input Bank IBAN.",
        "account_iban"=>"You completed beneficiary register successfully.");

        private $bank_json =array(
            "beneficiary_id"=>"Please input number to select Bank account.",
            "subbank"=>"Please input number to select currency.",
            "currency_id"=>"Please input amount.",
            "amount"=>"Please select payment type.",
            "payment_type"=>"Please input description.",
            "des"=>"You completed Bank Transfer successfully."
            );
        private $internal_json =array(
            "email"=>"Please input number to select Wallet.",
            "wallet_id"=>"Please input amount.",
            "amount"=>"Please input description.",
            "description"=>"You completed Internal Transfer successfully."
            );
        private $request_json =array(
            "account_email"=>"Please input number to select Currency.",
            "currency_id"=>"Please input Account Name to request money.",
            "account_name"=>"Please input amount to request money",
            "amount" => "Please input description.",
            "description"=>"You completed Request Money successfully."
            );
        private $crypto_withdraw_json =array(
            "currency_id" => "Please input amount to withdraw.",
            "amount" => "Please input sender address.",
            "sender_address" => "Please input description.",
            "description"=>"You completed Crypto Withdraw successfully."
            );
        private $exchange_json =array(
            "from_wallet_id" => "Please input amount to withdraw.",
            "amount" => "Please input number to convert wallet type.",
            "wallet_type" => "Please input number to select convert currency.",
            "to_wallet_id"=>"You completed exchange successfully."
            );
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['bot_login', 'bot_logout', 'inbound', 'crypto_deposit_sms']]);
    }

    public function index()
    {
        $data['telegram'] = UserTelegram::where('user_id',auth()->id())->first();
        $data['whatsapp'] = UserWhatsapp::where('user_id',auth()->id())->first();
        return view('user.staff.pincode',$data);
    }

    public function inbound(Request $request)
    {
        $data = $request->all();
        $telegram_hook = new BotWebhook();
        $telegram_hook->name = 'telegram';
        $telegram_hook->payload = json_decode($request->getContent());
        $telegram_hook->url = route('user.telegram.inbound');
        $telegram_hook->save();

        $gs = Generalsetting::first();
        $text = $data['message']['text'];

        $telegram_user = UserTelegram::where('chat_id', $data['message']['chat']['id'])->where('status', 1)->first();
        $chat_id = $data['message']['chat']['id'];
        if($telegram_user && $telegram_user->status == 1  && $telegram_user->user_id != 0) {
            $w_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
            if ($w_session != null && $w_session->data != null && $w_session->type == "Beneficiary") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from beneficiary register. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                if($final == null) {
                    if ( $text == 'Individual' || $text == 'Corporate') {
                        $question = $text == 'Individual' ? $this->beneficiary_json : $this->beneficiary_company_json;
                        $to_message = $question['type'];
                        $dump = $w_session->data;
                        $dump->type = $text == 'Individual' ? 'RETAIL' : 'CORPORATE';
                        $w_session->data = $dump;
                        $w_session->save();
                    }
                    else {
                        $to_message = "Please select correctly.";
                    }
                }
                else {
                    $question = $w_session->data->type == 'RETAIL' ? $this->beneficiary_json : $this->beneficiary_company_json;
                    $next_key = prefix_get_next_key_array($question, $final);
                    if($next_key == "email") {
                        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                            $to_message = "Please input correct email.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "account_iban") {
                        $client = new Client();
                        try {
                            $url = 'https://api.ibanapi.com/v1/validate/'.$text.'?api_key='.$gs->ibanapi;
                            $response = $client->request('GET', $url);
                            $bank = json_decode($response->getBody());
                            //code...
                        } catch (RequestException  $e) {
                            Log::info($e->getResponse()->getBody());
                            send_message_telegram(json_decode($e->getResponse()->getBody())->message."\n Please input IBAN correctly.", $chat_id);
                            return;
                        }
                        if (isset($bank->data->bank)) {
                            $dump = $w_session->data;
                            $dump->account_iban = $text;
                            $dump->bank_address = $bank->data->bank->address;
                            $dump->bank_name = $bank->data->bank->bank_name;
                            $dump->swift_bic = $bank->data->bank->bic;
                            $w_session->data = $dump;
                            $w_session->save();
                            $beneficiary = new Beneficiary();
                            $input = json_decode(json_encode($w_session->data), true);

                            $input['user_id'] = $w_session->user_id;
                            if($w_session->data->type == 'RETAIL') {
                                $input['name'] =  trim($w_session->data->first_name)." ".trim($w_session->data->last_name);
                            }
                            else {
                                $input['name'] =  $w_session->data->company_name;
                            }
                            $beneficiary->fill($input)->save();
                            $w_session->data = null;
                            $w_session->save();

                            send_message_telegram('You completed beneficiary register successfully.', $chat_id);
                            return;
                        }
                        else {
                            send_message_telegram('Please input IBAN correctly', $chat_id);
                            return;
                        }
                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];
                }
                send_message_telegram($to_message, $chat_id);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "BankTransfer") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Bank Transfer successfully. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                if($final == null) {
                    $ids = Beneficiary::where('user_id',  $telegram_user->user_id)->pluck('id')->toArray();

                    if (in_array($text, $ids)) {
                        $question = $this->bank_json;
                        $banks = SubInsBank::where('status', 1)->get();
                        $bank_ids = '';
                        foreach ($banks as $key => $bank) {
                            $bank_ids = $bank_ids.$bank->id.':'.$bank->name."\n";
                        }
                        if(strlen($bank_ids) == 0) {
                            $to_message = "You have no activated Bank. Please contact support team.";
                            $w_session->data = null;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        $to_message = $question['beneficiary_id']."\n".$bank_ids;
                        $dump = $w_session->data;
                        $dump->beneficiary_id = $text;
                        $w_session->data = $dump;
                        $w_session->save();

                    }
                    else {
                        $to_message = "Please input number to select Beneficiary correctly.";
                    }
                }
                else {
                    $question = $this->bank_json;
                    $next_key = prefix_get_next_key_array($question, $final);
                    if($next_key == "subbank") {
                        $banks = SubInsBank::where('status', 1)->pluck('id')->toArray();
                        if (in_array($text, $banks)) {
                            $subbank = SubInsBank::find($text);
                            if($subbank->hasGateway()){
                                $currencies = BankAccount::whereUserId($telegram_user->user_id)->where('subbank_id', $text)->with('currency')->get();
                            } else {
                                $currencies = BankPoolAccount::where('bank_id', $text)->with('currency')->get();
                            }
                            $currency_list = '';
                            foreach ($currencies as $key => $currency) {
                                $currency_list = $currency_list.$currency->currency->id.':'.$currency->currency->code;
                            }
                            if(strlen($currency_list) == 0) {
                                $to_message = "You have no registered Bank Account. Please create bank account in Web Dashboard. Or Please select other Bank Account.";
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }

                            $to_message = $question['subbank']."\n".$currency_list;
                            $dump = $w_session->data;
                            $dump->subbank = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = "Please input number to select Bank account correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "currency_id") {
                        $subbank = SubInsBank::find($w_session->data->subbank);
                        if($subbank->hasGateway()){
                            $currencies = BankAccount::whereUserId($telegram_user->user_id)->where('subbank_id', $w_session->data->subbank)->with('currency')->get();
                        } else {
                            $currencies = BankPoolAccount::where('bank_id', $w_session->data->subbank)->with('currency')->get();
                        }
                        $currency_list = [];
                        foreach ($currencies as $key => $currency) {
                            array_push($currency_list, $currency->currency->id);
                        }
                        if (in_array($text, $currency_list)) {

                            $to_message = $question['currency_id'];
                            $dump = $w_session->data;
                            $dump->currency_id = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = "Please input number to select currency correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "amount") {
                        if (!is_numeric($text)) {
                            $to_message = "Please input number for amount correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        if ($text >= $gs->other_bank_limit) {
                            $to_message = "Please input less amount than ".$gs->other_bank_limit;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        $user = User::findOrFail($telegram_user->user_id);

                        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
                        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
                        $dailySend = BalanceTransfer::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
                        $monthlySend = BalanceTransfer::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

                        if($dailySend > $global_range->daily_limit){
                            $to_message = "Daily send limit over.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($monthlySend > $global_range->monthly_limit){
                            $to_message = "Monthly send limit over.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }



                        $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId($user->id)->whereDate('created_at', now())->get();
                        $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId($user->id)->whereMonth('created_at', now()->month())->get();
                        $transaction_global_cost = 0;
                        $currency = Currency::findOrFail($w_session->data->currency_id);
                        $rate = getRate($currency);
                        $transaction_global_fee = check_global_transaction_fee($text/$rate, $user, 'withdraw');

                        if($transaction_global_fee)
                        {
                            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($text/($rate*100)) * $transaction_global_fee->data->percent_charge;
                        }
                        $finalAmount = $text + $transaction_global_cost*$rate;

                        if($global_range->min > $text/$rate){
                            $to_message = 'Request Amount should be greater than this '.$global_range->min;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($global_range->max < $text/$rate){
                            $to_message = 'Request Amount should be less than this '.$global_range->max;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        $balance = user_wallet_balance($user->id, $w_session->data->currency_id);

                        if($balance < 0 || $finalAmount > $balance){
                            $to_message = 'Insufficient Balance!';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($global_range->daily_limit <= $finalAmount){
                            $to_message = 'Your daily limitation of transaction is over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                            $to_message = 'Your daily limitation of transaction is over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }


                        if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                            $to_message = 'Your monthly limitation of transaction is over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }


                        $to_message = $question['amount'];
                        $extra_string = "\nSWIFT\nSEPA\nSEPA_INSTANT";
                        if ($currency->code == 'EUR') {
                            $extra_string = "\nSWIFT\nSEPA\nSEPA_INSTANT";
                        }
                        else {
                            $extra_string = "\nSWIFT\nCHAPS";
                        }
                        $dump = $w_session->data;
                        $dump->cost = $transaction_global_cost*$rate;
                        $dump->final_amount = (float)$text;
                        $dump->amount = $text + $transaction_global_cost*$rate;
                        $w_session->data = $dump;
                        $w_session->save();
                        send_message_telegram($to_message.$extra_string, $chat_id);
                        return;
                    }
                    if($next_key == "payment_type") {
                        $currency = Currency::findOrFail($w_session->data->currency_id);
                        if ($currency->code == 'EUR'){
                            $currency_list = ['SEPA', 'SWIFT', 'SEPA_INSTANT'];
                        }
                        else {
                            $currency_list = ['SWIFT', 'CHAPS'];
                        }

                        if (in_array($text, $currency_list)) {
                            $to_message = $question['payment_type'];
                            $dump = $w_session->data;
                            $dump->payment_type = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = "Please select payment type correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "des") {
                        $dump = $w_session->data;
                        $dump->des = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                        $beneficiary = Beneficiary::findOrFail($w_session->data->beneficiary_id);

                        $balance_transfer = new BalanceTransfer();



                        $txnid = Str::random(4).time();

                        $balance_transfer->user_id = $telegram_user->user_id;
                        $balance_transfer->transaction_no = $txnid;
                        $balance_transfer->currency_id = $w_session->data->currency_id;
                        $balance_transfer->subbank = $w_session->data->subbank;
                        $balance_transfer->iban = $beneficiary->account_iban;
                        $balance_transfer->swift_bic = $beneficiary->swift_bic;
                        $balance_transfer->beneficiary_id = $w_session->data->beneficiary_id;
                        $balance_transfer->type = 'other';
                        $balance_transfer->cost = $w_session->data->cost;
                        $balance_transfer->payment_type = $w_session->data->payment_type;
                        $balance_transfer->amount = $w_session->data->amount;
                        $balance_transfer->final_amount = $w_session->data->final_amount;
                        $balance_transfer->description = $w_session->data->des;
                        $balance_transfer->status = 0;
                        $balance_transfer->save();

                        $w_session->data = null;
                        $w_session->save();

                        user_wallet_decrement($telegram_user->user_id, $balance_transfer->currency_id, $balance_transfer->amount);
                        user_wallet_increment(0, $balance_transfer->currency_id, $balance_transfer->cost, 9);

                        send_message_telegram('You completed Bank Transfer successfully.', $chat_id);
                        $user = User::findOrFail($telegram_user->user_id);
                        $currency = Currency::findOrFail($balance_transfer->currency_id);
                        $subbank = SubInsBank::findOrFail($balance_transfer->subbank);
                        mailSend('create_withdraw',['amount'=>amount($balance_transfer->final_amount,1,2), 'trnx'=> $balance_transfer->transaction_no,'curr' => $currency->code,'method'=>$subbank->name,'charge'=> amount($balance_transfer->cost,1,2),'date_time'=> dateFormat($balance_transfer->created_at)], $user);
                        send_notification($user->id, 'Bank transfer has been created on Telegram by '.($user->company_name ?? $user->name).".\n Amount is ".$currency->symbol.$balance_transfer->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($balance_transfer->cost,1,2)."\n Transaction ID:".$balance_transfer->transaction_no."\n Status:Pending", route('admin-user-banks', $user->id));

                        send_staff_telegram('Bank transfer has been created on Telegram by '.($user->company_name ?? $user->name).".\n Amount is ".$currency->symbol.$balance_transfer->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($balance_transfer->cost,1,2)."\n Transaction ID:".$balance_transfer->transaction_no."\n Status:Pending"."\n Please check.".route('admin-user-banks', $user->id), 'Bank Transfer');

                        return;

                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];

                }
                send_message_telegram($to_message, $chat_id);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "Beneficiary_Simple") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from beneficiary register. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $feed = explode(';', $text);
                if (count($feed) != 9) {
                    $to_message = "You missed some value , Please check and input again.";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $beneficiary = new Beneficiary();
                if (trim($feed[0]) == 'Individual' || trim($feed[0]) == 'Corporate') {
                    $beneficiary->type = trim($feed[0]) == 'Individual' ? 'RETAIL' : 'CORPORATE';
                }
                else {
                    $to_message = "Please input Beneficiary Type : Individaul \ Corporate.";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                if (trim($feed[0]) == 'Individual' && str_contains(trim($feed[1]), ' ')) {
                    $beneficiary->name = trim($feed[1]);
                }
                elseif (trim($feed[0]) == 'Corporate') {
                    $beneficiary->name = trim($feed[1]);
                }
                else {
                    $to_message = "Please input Individual Name : FirstName LastName.";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $client = new Client();
                try {
                    $url = 'https://api.ibanapi.com/v1/validate/'.$feed[8].'?api_key='.$gs->ibanapi;
                    $response = $client->request('GET', $url);
                    $bank = json_decode($response->getBody());
                    //code...
                } catch (RequestException  $e) {
                    Log::info($e->getResponse()->getBody());
                    send_message_telegram(json_decode($e->getResponse()->getBody())->message."\n Please input IBAN correctly.", $chat_id);
                    return;
                }
                if (isset($bank->data->bank)) {
                    $beneficiary->bank_address = $bank->data->bank->address;
                    $beneficiary->bank_name = $bank->data->bank->bank_name;
                    $beneficiary->swift_bic = $bank->data->bank->bic;
                    $beneficiary->account_iban = trim($feed[8]);
                    if (!filter_var(trim($feed[2]), FILTER_VALIDATE_EMAIL)) {
                        $to_message = "Please input correct email.";
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    $beneficiary->email = trim($feed[2]);
                    $beneficiary->user_id = $w_session->user_id;
                    $beneficiary->address = trim($feed[4]);
                    $beneficiary->phone = trim($feed[3]);
                    $beneficiary->registration_no = trim($feed[5]);
                    $beneficiary->vat_no = trim($feed[6]);
                    $beneficiary->contact_person = trim($feed[7]);

                    $beneficiary->save();
                    $w_session->data = null;
                    $w_session->save();

                    send_message_telegram('You completed beneficiary register successfully.', $chat_id);
                    return;
                }
                else {
                    send_message_telegram('Please input IBAN correctly', $chat_id);
                    return;
                }

            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "InternalTransfer") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Internal Transfer successfully. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                $question = $this->internal_json;
                if($final == null) {
                    if (!filter_var(trim($text), FILTER_VALIDATE_EMAIL)) {
                        $to_message = "Please input correct email.";
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    else {
                        $user = User::findOrFail($w_session->user_id);
                        if($text == $user->email){
                            $to_message = "This email is yours.You can not send money yourself!";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        $userType = explode(',', $user->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                        $modules = explode(" , ", $user->modules);
                        if (in_array('Crypto',$modules)) {
                          $wallet_type_list['8'] = 'Crypto';
                        }
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', $w_session->user_id)->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }
                        $wallets = Wallet::where('user_id',$w_session->user_id)->with('currency')->get();
                        $wallet_list = '';
                        $currency = Currency::findOrFail(defaultCurr());
                        foreach ($wallets as $key => $wallet) {
                            if (isset($wallet_type_list[$wallet->wallet_type])){
                                if($wallet->currency->type == 2) {
                                    $amount = amount(Crypto_Balance($wallet->user_id, $wallet->currency_id), 2);
                                    $amount_fiat = amount(Crypto_Balance_Fiat($wallet->user_id, $wallet->currency_id), 1);
                                    $amount = $amount.' ('.$amount_fiat.$currency->code.')';

                                }
                                else {
                                    $amount = amount($wallet->balance,$wallet->currency->type,2);
                                }
                                if ($amount > 0) {
                                    $wallet_list = $wallet_list.$wallet->id.':'.$wallet->currency->code.' -- '.$amount.' -- '.$wallet_type_list[$wallet->wallet_type]."\n";
                                }
                            }
                        }
                        $to_message = $question['email']."\n".$wallet_list;
                        $dump = $w_session->data;
                        $dump->email = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                    }
                }
                else {
                    $next_key = prefix_get_next_key_array($question, $final);
                    $user = User::findOrFail($w_session->user_id);
                    if($next_key == "wallet_id") {
                        $wallets = Wallet::where('user_id',$w_session->user_id)->pluck('id')->toArray();
                        if (in_array($text, $wallets)) {
                            $wallet = Wallet::find($text);

                            $to_message = $question['wallet_id'];
                            $dump = $w_session->data;
                            $dump->wallet_id = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = "Please input number to select Wallet correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "amount") {
                        if (!is_numeric($text)) {
                            $to_message = "Please input number for amount correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }


                        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
                        $dailySend = BalanceTransfer::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
                        $monthlySend = BalanceTransfer::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');
                        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();

                        if($dailySend > $global_range->daily_limit){
                            $to_message = "Daily send limit over.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        if($monthlySend > $global_range->monthly_limit){
                            $to_message = "Monthly send limit over.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }



                        $transaction_global_cost = 0;
                        $wallet = Wallet::where('id', $w_session->data->wallet_id)->with('currency')->first();

                        $currency = Currency::findOrFail($wallet->currency_id);
                        $rate = getRate($currency);
                        $transaction_global_fee = check_global_transaction_fee($text/$rate, $user, 'send');

                        if($transaction_global_fee)
                        {
                            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($text/($rate*100)) * $transaction_global_fee->data->percent_charge;
                        }
                        $finalAmount = $text + $transaction_global_cost*$rate;

                        if($global_range->min > $text/$rate){
                            $to_message = 'Request Amount should be greater than this '.$global_range->min;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($global_range->max < $text/$rate){
                            $to_message = 'Request Amount should be less than this '.$global_range->max;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        if ($wallet->currency->type == 2) {
                            if($finalAmount > Crypto_Balance($user->id, $currency->id)){
                                $to_message = 'Insufficient Balance!';
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }
                        else {
                            if($finalAmount > user_wallet_balance($user->id, $currency->id, $wallet->wallet_type)){
                                $to_message = 'Insufficient Balance!';
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }

                        if($global_range->daily_limit <= $finalAmount){
                            $to_message = 'Your daily limitation of transaction is over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }






                        $to_message = $question['amount'];
                        $dump = $w_session->data;
                        $dump->cost = $transaction_global_cost*$rate;
                        $dump->amount = $text + $transaction_global_cost*$rate;
                        $w_session->data = $dump;
                        $w_session->save();
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    if($next_key == "description") {

                        $wallet = Wallet::where('id', $w_session->data->wallet_id)->with('currency')->first();
                        $currency = Currency::findOrFail($wallet->currency_id);
                        $rate = getRate($currency);

                        if ($wallet->currency->type == 2) {
                            $towallet = get_wallet(0, $wallet->currency_id, 9);

                            if($wallet->currency->code == 'ETH') {
                                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                                $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($w_session->data->cost*$rate*pow(10,18)).'"}';
                                $res = RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not send money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            else if($wallet->currency->code == 'BTC') {
                                $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($w_session->data->cost*$rate, 2)],$wallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not send money because BTC has some issue. ". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            else {
                                RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                                $tokenContract = $wallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $w_session->data->cost*$rate, $wallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not send money because Ether has some issue.".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                        }
                        else {
                            user_wallet_increment(0, $wallet->currency_id, $w_session->data->cost*$rate, 9);
                        }


                        if($receiver = User::where('email',$w_session->data->email)->first()){
                            if ($wallet->currency->type == 2) {
                                $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                                if($wallet->currency->code == 'ETH') {
                                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex(($w_session->data->amount - $w_session->data->cost)*pow(10,18)).'"}';
                                    $res = RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                                    if($res == 'error') {
                                        $to_message = "You can not send money because Ether has some issue.";
                                        $w_session->data = null;
                                        $w_session->save();
                                        send_message_telegram($to_message, $chat_id);
                                        return;
                                    }
                                }
                                else if($wallet->currency->code == 'BTC') {
                                    $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount(($w_session->data->amount - $w_session->data->cost), 2)],$wallet->keyword);
                                    if (isset($res->error->message)){
                                        $to_message = "You can not send btc because have some issue: ".$res->error->message;
                                        $w_session->data = null;
                                        $w_session->save();
                                        send_message_telegram($to_message, $chat_id);
                                        return;
                                    }
                                }
                                else {
                                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                                    $tokenContract = $wallet->currency->address;
                                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, (float)($w_session->data->amount - $w_session->data->cost), $wallet->keyword);
                                    if (json_decode($result)->code == 1){
                                        $to_message =  'Ethereum client error: '.json_decode($result)->message;
                                        $w_session->data = null;
                                        $w_session->save();
                                        send_message_telegram($to_message, $chat_id);
                                        return;
                                    }
                                }
                            }
                            else{
                                user_wallet_decrement($user->id, $wallet->currency_id, $w_session->data->amount, $wallet->wallet_type);
                                user_wallet_increment($receiver->id, $wallet->currency_id, ($w_session->data->amount - $w_session->data->cost), $wallet->wallet_type);
                            }

                            $txnid = Str::random(4).time();
                            $data = new BalanceTransfer();
                            $data->user_id = $user->id;
                            $data->receiver_id = $receiver->id;
                            $data->transaction_no = $txnid;
                            $data->currency_id = $wallet->currency_id;
                            $data->type = 'own';
                            $data->cost = $w_session->data->cost;
                            $data->amount = $w_session->data->amount;
                            $data->description = $text;
                            $data->status = 1;
                            $data->save();


                            $trans = new Transaction();
                            $trans->trnx = $txnid;
                            $trans->user_id     = $user->id;
                            $trans->user_type   = 1;
                            $trans->currency_id = $wallet->currency_id;
                            $trans_wallet = get_wallet($user->id,  $wallet->currency_id, $wallet->wallet_type);
                            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                            $trans->amount      = $w_session->data->amount;
                            $trans->charge      = $w_session->data->cost;
                            $trans->type        = '-';
                            $trans->remark      = 'send';
                            $trans->details     = trans('Send Money');
                            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$text.'"}';
                            $trans->save();


                            $trans = new Transaction();
                            $trans->trnx = $txnid;
                            $trans->user_id     = $receiver->id;
                            $trans->user_type   = 1;
                            $trans->currency_id = $wallet->currency_id;
                            $trans->amount      = ($w_session->data->amount - $w_session->data->cost);
                            $trans_wallet = get_wallet($receiver->id, $wallet->currency_id, $wallet->wallet_type);
                            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                            $trans->charge      = 0;
                            $trans->type        = '+';
                            $trans->remark      = 'send';
                            $trans->details     = trans('Send Money');
                            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$text.'"}';
                            $trans->save();



                            mailSend('send_money',['amount'=>($w_session->data->amount - $w_session->data->cost), 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $trans->created_at ], $receiver);
                            send_notification($receiver->id, ($w_session->data->amount - $w_session->data->cost).$currency->code.' Money is sent from '.($user->company_name ?? $user->name).' to '.($receiver->company_name ?? $receiver->name )."\n Charge Fee : 0".$currency->code."\n Transaction ID : ".$txnid, route('admin-user-transactions', $receiver->id));
                            mailSend('send_money',['amount'=>$w_session->data->amount, 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=>  $w_session->data->cost, 'date_time'=> $trans->created_at ], $user);
                            send_notification($user->id, $w_session->data->amount.$currency->code.' Money is sent from '.($user->company_name ?? $user->name).' to '.($receiver->company_name ?? $receiver->name )."\n Charge Fee : ".$w_session->data->cost.$currency->code."\n Transaction ID : ".$txnid, route('admin-user-transactions', $user->id));

                            $w_session->data = null;
                            $w_session->save();
                            $to_message = $question['description'];
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }else{
                            $to_message = "Sender who is owner of this email: ". $w_session->data->email." not exist in our system.";
                            $w_session->data = null;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }


                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];

                }
                send_message_telegram($to_message, $chat_id);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "RequestMoney"){
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Request Money. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                $question = $this->request_json;
                if($final == null) {
                    if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                        $to_message = "Please input correct email.";
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    $user = User::findOrFail($w_session->user_id);
                    if($text == $user->email){
                        $to_message = "This email is yours.You can not request money yourself!";
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    $to_message = $question['account_email'];
                    $currencies = Currency::where('status', 1)->get();
                    foreach($currencies as $currency) {
                        $to_message = $to_message."\n".$currency->id.":".$currency->code;
                    }
                    $dump = $w_session->data;
                    $dump->account_email = $text;
                    $w_session->data = $dump;
                    $w_session->save();

                }
                else {
                    $next_key = prefix_get_next_key_array($question, $final);
                    $user = User::findOrFail($w_session->user_id);
                    if($next_key == "currency_id") {
                        $currency_ids = Currency::where('status', 1)->pluck('id')->toArray();
                        if(in_array($text, $currency_ids)) {
                            $to_message = $question['currency_id'];
                            $dump = $w_session->data;
                            $dump->currency_id = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else{
                            $to_message = "Please input number to select currency correctly.";
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    if($next_key == "amount") {
                        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
                        $dailyRequests = MoneyRequest::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
                        $monthlyRequests = MoneyRequest::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
                        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'recieve')->first();


                        $receiver = User::where('email',$w_session->data->account_email)->first();
                        $currency = Currency::findOrFail($w_session->data->currency_id);
                        $rate = getRate($currency);

                        if($dailyRequests > $global_range->daily_limit){
                            $to_message = 'Daily request limit over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($monthlyRequests > $global_range->monthly_limit){
                            $to_message = 'Monthly request limit over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if ($text/$rate < $global_range->min || $text/$rate > $global_range->max) {
                            $to_message = 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        $transaction_global_fee = check_global_transaction_fee($text/$rate, $user, 'recieve');
                        $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($text/($rate * 100)) * $transaction_global_fee->data->percent_charge;
                        $transaction_custom_cost = 0;
                        if($user->referral_id != 0)
                        {
                            $transaction_custom_fee = check_custom_transaction_fee($text/$rate, $user, 'recieve');
                            if($transaction_custom_fee) {
                                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($text/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                            }
                        }
                        $to_message = $question['amount'];
                        $dump = $w_session->data;
                        $dump->receiver_id = $receiver === null ? 0 : $receiver->id;
                        $dump->cost = $transaction_global_cost*$rate;
                        $dump->supervisor_cost = $transaction_custom_cost*$rate;
                        $dump->amount = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                        send_message_telegram($to_message, $chat_id);
                        return;


                    }
                    if($next_key == "description") {
                        $txnid = Str::random(4).time();

                        $data = new MoneyRequest();
                        $data->user_id = $w_session->user_id;
                        $data->receiver_id = $w_session->data->receiver_id;
                        $data->receiver_name = $w_session->data->account_name;
                        $data->receiver_email = $w_session->data->account_email;
                        $data->transaction_no = $txnid;
                        $data->currency_id = $w_session->data->currency_id;
                        $data->cost = $w_session->data->cost;
                        $data->supervisor_cost = $w_session->data->supervisor_cost;
                        $data->amount = $w_session->data->amount;
                        $data->status = 0;
                        $data->details = $text;
                        $data->user_type = 1;
                        $data->save();

                        $w_session->data = null;
                        $w_session->save();

                        $currency = Currency::findOrFail($data->currency_id);
                        send_notification($user->id, ($user->company_name ?? $user->name).' send request money to '.$data->receiver_email."\n Amount is ".$data->amount.$currency->code , route('admin.request.money'));

                        if($data->receiver_id == 0){
                            $to =  $data->receiver_email;
                            $subject = " Money Request";
                            $url =     "<button style='height: 50;width: 200px;' ><a href='".route('user.money.request.new', encrypt($txnid))."' target='_blank' type='button' style='color: #2C729E; font-weight: bold; text-decoration: none; '>Confirm</a></button>";

                            $msg_body = '
                            <!DOCTYPE html>
                            <html lang="en-US">
                                <head>
                                    <meta charset="utf-8"><title>Request Money</title>
                                </head>
                                <body>
                                    <p> Hello '.$data->receiver_name.'.</p>
                                    <p> You received request money ('.$data->amount.$currency->code.').</p>
                                    <p> Please confirm current.</p>
                                    '.$url.'
                                    <p> Thank you.</p>

                                </body>
                            </html>
                            ';

                            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                            $headers .= "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                            // More headers

                            sendMail($to,$subject,$msg_body,$headers);
                            $to_message = $question['description'];
                            send_message_telegram($to_message, $chat_id);
                            return;

                        }
                        else {
                            $receiver = User::findOrFail($data->receiver_id);
                            mailSend('request_money_sent',['amount'=>$data->amount, 'curr' => $currency->code, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $data->created_at ], $receiver);

                            $to_message = $question['description'];
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];
                }
                send_message_telegram($to_message, $chat_id);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "CryptoWithdraw"){
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Crypto Withdraw. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                $question = $this->crypto_withdraw_json;
                if($final == null) {
                    $user = User::findOrFail($w_session->user_id);
                    $wallet_list = Wallet::where('user_id',$user->id)->where('user_type',1)->where('wallet_type', 8)->pluck('id')->toArray();
                    if(in_array($text, $wallet_list)) {
                        $wallet = Wallet::where('id', $text)->with('currency')->first();
                        $dump = $w_session->data;
                        $dump->currency_id = $wallet->currency->id;
                        $w_session->data = $dump;
                        $w_session->save();
                        $to_message = $question['currency_id'];
                    }
                    else {
                        $to_message = "Please input number to select wallet correctly.";
                    }
                }
                else {
                    $next_key = prefix_get_next_key_array($question, $final);
                    $user = User::findOrFail($w_session->user_id);
                    if($next_key == "amount") {
                        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
                        $dailyRequests = MoneyRequest::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('success')->sum('amount');
                        $monthlyRequests = MoneyRequest::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('success')->sum('amount');
                        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();


                        $currency = Currency::findOrFail($w_session->data->currency_id);
                        $rate = getRate($currency);
                        $userBalance = Crypto_Balance($user->user_id, $currency->id);

                        if($dailyRequests > $global_range->daily_limit){
                            $to_message = 'Daily request limit over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if($monthlyRequests > $global_range->monthly_limit){
                            $to_message = 'Monthly request limit over.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                        if ($text/$rate < $global_range->min || $text/$rate > $global_range->max) {
                            $to_message = 'Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min ;
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }




                        $transaction_global_fee = check_global_transaction_fee($text/$rate, $user, 'withdraw_crypto');
                        $transaction_global_cost = 0;
                        if($transaction_global_fee)
                        {

                            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($text/($rate * 100)) * $transaction_global_fee->data->percent_charge;
                        }

                        if(($text + $transaction_global_cost*$rate) > Crypto_Balance($user->id, $currency->id)){
                            $to_message = 'Insufficient Balance!';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        $to_message = $question['amount'];
                        $dump = $w_session->data;
                        $dump->global_cost = $transaction_global_cost*$rate;
                        $dump->amount = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                        send_message_telegram($to_message, $chat_id);
                        return;


                    }
                    if($next_key == "description") {
                        $currency = Currency::findOrFail($w_session->data->currency_id);
                        $fromWallet = Wallet::where('user_id', $user->id)->where('wallet_type', 8)->where('currency_id', $currency->id)->with('currency')->first();
                        $toWallet = get_wallet(0,$currency->id,9);
                        if($currency->code == 'ETH') {
                            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$toWallet->wallet_no.'", "value": "0x'.dechex( $w_session->data->global_cost*pow(10,18)).'"}';
                            $res = RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
                            if($res == 'error') {
                                $to_message = "You can not withdraw money because Ether has some issue.";
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }
                        elseif($currency->code == 'BTC') {
                            $res = RPC_BTC_Send('sendtoaddress',[$toWallet->wallet_no, amount($w_session->data->global_cost, 2)],$fromWallet->keyword);
                            if (isset($res->error->message)){
                                $to_message = "You can not withdraw money because BTC has some issue.". $res->error->message;
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }
                        else{
                            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                            $tokenContract = $fromWallet->currency->address;
                            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $toWallet->wallet_no, $w_session->data->global_cost,  $fromWallet->keyword);
                            if (json_decode($result)->code == 1){
                                $to_message = "You can not withdraw money because Ether has some issue.".json_decode($result)->message;
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }

                        if($currency->code == 'ETH') {
                            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                            $tx = '{"from": "'.$fromWallet->wallet_no.'", "to": "'.$w_session->data->sender_address.'", "value": "0x'.dechex($w_session->data->amount*pow(10,18)).'"}';
                            $res = RPC_ETH_Send('personal_sendTransaction',$tx, $fromWallet->keyword ?? '');
                            if($res == 'error') {
                                $to_message = "You can not withdraw money because Ether has some issue.";
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                            $trnx = $res;
                        }
                        else if($fromWallet->currency->code == 'BTC') {
                            $res = RPC_BTC_Send('sendtoaddress',[$w_session->data->sender_address, amount($w_session->data->amount, 2)],$fromWallet->keyword);
                            if (isset($res->error->message)){
                                $to_message = "You can not withdraw money because BTC has some issue.". $res->error->message;
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                            $trnx = $fromWallet->wallet_no;
                        }
                        else {
                            RPC_ETH('personal_unlockAccount',[$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                            $tokenContract = $fromWallet->currency->address;
                            $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $w_session->data->sender_address, $w_session->data->amount,  $fromWallet->keyword);
                            if (json_decode($result)->code == 1){
                                $to_message = "You can not withdraw money because Ether has some issue.".json_decode($result)->message;
                                $w_session->data = null;
                                $w_session->save();
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                            $trnx = json_decode($result)->message;
                        }


                        $cryptowithdraw = new CryptoWithdraw();
                        $cryptowithdraw->currency_id = $w_session->data->currency_id;
                        $cryptowithdraw->user_id = $w_session->user_id;
                        $cryptowithdraw->amount = $w_session->data->amount ;
                        $cryptowithdraw->sender_address = $w_session->data->sender_address;
                        $cryptowithdraw->hash = $trnx ;
                        $cryptowithdraw->status = 1 ;
                        $cryptowithdraw->save();



                        $txnid = Str::random(12);


                        $trans = new Transaction();
                        $trans->trnx = $txnid;
                        $trans->user_id     = $user->id;
                        $trans->user_type   = 1;
                        $trans->currency_id = $cryptowithdraw->currency_id;
                        $trans->amount      = $w_session->data->amount + $w_session->data->global_cost ;
                        $trans->charge      = $w_session->data->global_cost ;

                        $trans_wallet = get_wallet($user->id, $cryptowithdraw->currency_id, 8);
                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                        $trans->type        = '-';
                        $trans->remark      = 'withdraw_crypto';
                        $trans->details     = trans('Withdraw money');
                        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$w_session->data->sender_address.'"}';
                        $trans->save();

                        $w_session->data = null;
                        $w_session->save();

                        $to_message = $question['description'];
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];
                }
                send_message_telegram($to_message, $chat_id);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "Exchange"){
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Exchange. ";
                    send_message_telegram($to_message, $chat_id);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                $question = $this->exchange_json;
                if($final == null) {
                    $user = User::findOrFail($w_session->user_id);
                    $wallet_list = Wallet::where('user_id',$w_session->user_id)->with('currency')->pluck('id')->toArray();
                    if(in_array($text, $wallet_list)) {
                        $dump = $w_session->data;
                        $dump->from_wallet_id = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                        $to_message = $question['from_wallet_id'];
                    }
                    else {
                        $to_message = "Please input number to select wallet correctly.";
                    }
                }
                else {
                    $next_key = prefix_get_next_key_array($question, $final);
                    $user = User::findOrFail($w_session->user_id);
                    if($next_key == "amount") {
                        $fromWallet = Wallet::where('id', $w_session->data->from_wallet_id)->where('user_id', $user->id)->where('user_type', 1)->firstOrFail();



                        if ($fromWallet->currency->type == 2) {
                            if ($text > Crypto_Balance($user->id, $fromWallet->currency->id)) {
                                $to_message = 'Insufficient Balance!';
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        } else {
                            if ($text > $fromWallet->balance) {
                                $to_message = 'Insufficient Balance!';
                                send_message_telegram($to_message, $chat_id);
                                return;
                            }
                        }

                        $userType = explode(',', $user->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                        $modules = explode(" , ", $user->modules);
                        if (in_array('Crypto',$modules)) {
                          $wallet_type_list['8'] = 'Crypto';
                        }
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', $w_session->user_id)->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }
                        $message_list = '';
                        foreach($wallet_type_list as $key => $value) {
                            $message_list = $message_list.$key.' : '.$value."\n";
                        }


                        $to_message = $question['amount']."\n".$message_list;
                        $dump = $w_session->data;
                        $dump->amount = $text;
                        $w_session->data = $dump;
                        $w_session->save();
                        send_message_telegram($to_message, $chat_id);
                        return;
                    }
                    if($next_key == "wallet_type"){
                        $userType = explode(',', $user->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                        $modules = explode(" , ", $user->modules);
                        if (in_array('Crypto',$modules)) {
                          $wallet_type_list['8'] = 'Crypto';
                        }
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', $w_session->user_id)->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }

                        if(isset($wallet_type_list[$text])) {
                            if($text == 8) {
                                $currencies = Currency::where('status', 1)->whereType('2')->get();
                                $message_list = '';
                                foreach($currencies as $value) {
                                    $message_list = $message_list.$value->id.' : '.$value->code."\n";
                                }
                            }
                            else {
                                $currencies = Currency::where('status', 1)->whereType('1')->get();
                                $message_list = '';
                                foreach($currencies as $value) {
                                    $message_list = $message_list.$value->id.' : '.$value->code."\n";
                                }
                            }
                            $to_message = $question['wallet_type']."\n".$message_list;
                            $dump = $w_session->data;
                            $dump->wallet_type = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = 'Please input number to convert wallet type correctly';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }

                    }
                    if($next_key == "to_wallet_id") {
                        if($w_session->data->wallet_type == 8) {
                            $currencies =  Currency::where('status', 1)->whereType('2')->pluck('id')->toArray();
                        }
                        else {
                            $currencies =  Currency::where('status', 1)->whereType('1')->pluck('id')->toArray();
                        }
                        if(in_array($text, $currencies)) {
                            $dump = $w_session->data;
                            $dump->to_wallet_id = $text;
                            $w_session->data = $dump;
                            $w_session->save();

                            $fromWallet = Wallet::where('id', $w_session->data->from_wallet_id)->where('user_id', $user->id)->where('user_type', 1)->firstOrFail();

                            $toWallet = Wallet::where('currency_id', $w_session->data->to_wallet_id)->where('user_id', $user->id)->where('wallet_type', $w_session->data->wallet_type)->where('user_type', 1)->first();
                            $currency = Currency::findOrFail($w_session->data->to_wallet_id);
                            if (!$toWallet) {
                                if ($currency->type == 2) {
                                    if ($currency->code == 'BTC') {
                                        $keyword = str_rand();
                                        $address = RPC_BTC_Create('createwallet', [$keyword]);
                                    } else if ($currency->code == 'ETH') {
                                        $keyword = str_rand(6);
                                        $address = RPC_ETH('personal_newAccount', [$keyword]);
                                    } else {
                                        $eth_currency = Currency::where('code', 'ETH')->first();
                                        $eth_wallet = Wallet::where('user_id', $user->id)->where('wallet_type', $w_session->data->wallet_type)->where('currency_id', $eth_currency->id)->first();
                                        if (!$eth_wallet) {
                                            $to_message = 'Now, You do not have Eth Crypto Wallet. You have to create Eth Crypto wallet firstly for this exchange action .';
                                            $w_session->data = null;
                                            $w_session->save();
                                            send_message_telegram($to_message, $chat_id);
                                            return;
                                        }
                                        $address = $eth_wallet->wallet_no;
                                        $keyword = $eth_wallet->keyword;
                                    }
                                    if ($address == 'error') {
                                        return back()->with('error', 'You can not create this wallet because there is some issue in crypto node.');
                                    }
                                } else {
                                    $address = $gs->wallet_no_prefix . date('ydis') . random_int(100000, 999999);
                                    $keyword = '';
                                }
                                $toWallet = Wallet::create([
                                    'user_id' => $user->wallet_id,
                                    'user_type' => 1,
                                    'currency_id' => $w_session->data->to_wallet_id,
                                    'balance' => 0,
                                    'wallet_type' => $w_session->data->wallet_type,
                                    'wallet_no' => $address,
                                    'keyword' => $keyword
                                ]);

                                if ($w_session->data->wallet_type == 2) {
                                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                                    if (!$chargefee) {
                                        $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                                    }

                                    $trans = new Transaction();
                                    $trans->trnx = str_rand();
                                    $trans->user_id = $user->id;
                                    $trans->user_type = 1;
                                    $trans->currency_id = defaultCurr();
                                    $trans->amount = 0;
                                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                                    $trans->charge = $chargefee->data->fixed_charge;
                                    $trans->type = '-';
                                    $trans->remark = 'card-issuance';
                                    $trans->details = trans('Card Issuance');
                                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                                    $trans->save();
                                } else {
                                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                                    if (!$chargefee) {
                                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                                    }

                                    $trans = new Transaction();
                                    $trans->trnx = str_rand();
                                    $trans->user_id = $user->id;
                                    $trans->user_type = 1;
                                    $trans->currency_id = defaultCurr();
                                    $trans->amount = 0;
                                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                                    $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;
                                    $trans->charge = $chargefee->data->fixed_charge;
                                    $trans->type = '-';
                                    $trans->remark = 'account-open';
                                    $trans->details = trans('Wallet Create');
                                    $trans->data = '{"sender":"' . ($user->company_name ?? $user->name) . '", "receiver":"' . $gs->disqus . '"}';
                                    $trans->save();
                                }
                                $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');


                                $def_currency = Currency::findOrFail(defaultCurr());
                                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => $wallet_type_list[$w_session->data->wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                                send_notification($user->id, 'New '.$wallet_type_list[$w_session->data->wallet_type].' Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$def_currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-accounts', $user->id));
                                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
                            }
                            $from_rate = getRate($fromWallet->currency);
                            $transaction_global_cost = 0;
                            if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
                                $transaction_global_fee = check_global_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange');
                            } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
                                $transaction_global_fee = check_global_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_c_f');
                            } else if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 2) {
                                $transaction_global_fee = check_global_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_f_c');
                            } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 2) {
                                $transaction_global_fee = check_global_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_c_c');
                            }
                            if ($transaction_global_fee) {
                                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($w_session->data->amount / ($from_rate * 100)) * $transaction_global_fee->data->percent_charge;
                            }

                            $transaction_custom_cost = 0;
                            $charge = $transaction_global_cost * $from_rate;
                            $totalAmount = $w_session->data->amount + $charge;

                            if ($fromWallet->currency->type == 2) {
                                if ($totalAmount > Crypto_Balance($user->id, $fromWallet->currency->id)) {
                                    $to_message = 'Insufficient balance to your ' . $fromWallet->currency->code . ' wallet';
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            } else {
                                if ($totalAmount > $fromWallet->balance) {
                                    $to_message = 'Insufficient balance to your ' . $fromWallet->currency->code . ' wallet';
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            $defaultAmount = $w_session->data->amount / $from_rate;
                            $finalAmount = $defaultAmount * getRate($toWallet->currency);
                            if ($toWallet->currency->type == 2) {
                                if ($finalAmount > Crypto_Balance(0, $toWallet->currency->id)) {
                                    $to_message = 'Insufficient balance to this system ' . $toWallet->currency->code . ' wallet, pleasae contact to admin.';
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }

                            if ($user->referral_id != 0) {
                                if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
                                    $transaction_custom_fee = check_custom_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange');
                                } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
                                    $transaction_custom_fee = check_custom_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_c_f');
                                } else if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 2) {
                                    $transaction_custom_fee = check_custom_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_f_c');
                                } else if ($fromWallet->currency->type == 2 && $toWallet->currency->type == 2) {
                                    $transaction_custom_fee = check_custom_transaction_fee($w_session->data->amount / $from_rate, $user, 'exchange_c_c');
                                }
                                if ($transaction_custom_fee) {
                                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($w_session->data->amount / ($from_rate * 100)) * $transaction_custom_fee->data->percent_charge;
                                }
                                $remark = 'Exchange_money_supervisor_fee';
                                if ($fromWallet->currency->type == 1) {

                                    if (check_user_type_by_id(4, $user->referral_id)) {
                                        user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 6);
                                        $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 6);
                                    } elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                                        $remark = 'Exchange_money_manager_fee';
                                        user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 10);
                                        $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 10);
                                    }
                                } else {
                                    user_wallet_increment($user->referral_id, $fromWallet->currency->id, $transaction_custom_cost * $from_rate, 8);

                                    $trans_wallet = get_wallet($user->referral_id, $fromWallet->currency->id, 8);
                                    if ($fromWallet->currency->code == 'ETH') {
                                        $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $fromWallet->currency_id)->first();

                                        RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                        $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $torefWallet->wallet_no . '", "value": "0x' . dechex($transaction_custom_cost * $from_rate * pow(10, 18)) . '"}';
                                        $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                                        if($res == 'error') {
                                            $to_message = "You can not exchange money because Ether has some issue.";
                                            $w_session->data = null;
                                            $w_session->save();
                                            send_message_telegram($to_message, $chat_id);
                                            return;
                                        }

                                    } elseif ($fromWallet->currency->code == 'BTC') {
                                        $torefWallet = Wallet::where('user_id', $user->referral_id)->where('wallet_type', 8)->where('currency_id', $fromWallet->currency_id)->first();
                                        $res = RPC_BTC_Send('sendtoaddress', [$torefWallet->wallet_no, amount($transaction_custom_cost * $from_rate, 2)], $fromWallet->keyword);
                                        if (isset($res->error->message)){
                                            $to_message = "You can not exchange money because BTC has some issue :".$res->error->message;
                                            $w_session->data = null;
                                            $w_session->save();
                                            send_message_telegram($to_message, $chat_id);
                                            return;
                                        }
                                    } else {
                                        RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                        $tokenContract = $fromWallet->currency->address;
                                        $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $trans_wallet->wallet_no, $transaction_custom_cost * $from_rate, $fromWallet->keyword);
                                        if (json_decode($result)->code == 1){
                                            $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                            $w_session->data = null;
                                            $w_session->save();
                                            send_message_telegram($to_message, $chat_id);
                                            return;
                                        }
                                    }
                                }
                                $supervisor_trnx = str_rand();

                                $trans = new Transaction();
                                $trans->trnx = $supervisor_trnx;
                                $trans->user_id = $user->referral_id;
                                $trans->user_type = 1;

                                $trans->wallet_id = isset($trans_wallet) ? $trans_wallet->id : null;

                                $trans->currency_id = $fromWallet->currency->id;
                                $trans->amount = $transaction_custom_cost * $from_rate;
                                $trans->charge = 0;
                                $trans->type = '+';
                                $trans->remark = $remark;
                                $trans->details = trans('Exchange Money');
                                $trans->data = '{"sender":"' . $gs->disqus . '", "receiver":"' . (User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name) . '"}';
                                $trans->save();

                            }

                            if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->type == 1) {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet1) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                                $tosystemwallet1->balance -= $finalAmount;
                                $tosystemwallet1->update();
                            }
                            if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->type == 1) {
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet1) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $res = RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, amount($totalAmount, 2)], $fromWallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                                $tosystemwallet1->balance -= $finalAmount;
                                $tosystemwallet1->update();
                            }
                            if ($toWallet->currency->code == 'ETH' && $fromWallet->currency->type == 1) {
                                $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$fromsystemwallet1) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                RPC_ETH('personal_unlockAccount', [$tosystemwallet->wallet_no, $tosystemwallet->keyword ?? '', 30]);
                                $tx = '{"from": "' . $tosystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $tosystemwallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                                $fromsystemwallet1->balance += $totalAmount;
                                $fromsystemwallet1->update();
                            }
                            if ($toWallet->currency->code == 'BTC' && $fromWallet->currency->type == 1) {
                                $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$fromsystemwallet1) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $res = RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, amount($finalAmount, 2)], $tosystemwallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                $fromsystemwallet1->balance += $totalAmount;
                                $fromsystemwallet1->update();
                            }
                            if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code == 'ETH') {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code == 'BTC') {
                                $res = RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, amount($totalAmount, 2)], $fromWallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code == 'BTC') {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');

                                }

                                $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                                $res = RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, amount($finalAmount, 2)], $fromsystemwallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code == 'ETH') {
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $res = RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, amount($totalAmount, 2)], $fromWallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                                RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
                                $tx = '{"from": "' . $fromsystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromsystemwallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code == 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->code != 'ETH' && $toWallet->currency->type == 2) {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tx = '{"from": "' . $fromWallet->wallet_no . '", "to": "' . $tosystemwallet->wallet_no . '", "value": "0x' . dechex($totalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromWallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
                                $tokenContract = $toWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract,  $fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code == 'BTC' && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $res = RPC_BTC_Send('sendtoaddress', [$tosystemwallet->wallet_no, amount($totalAmount, 2)], $fromWallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
                                $tokenContract = $toWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract,  $fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
                                if (json_decode($result)->code == 1){

                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }

                            if ($fromWallet->currency->type == 1 && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
                                $tosystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet1 = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$fromsystemwallet1) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                RPC_ETH('personal_unlockAccount', [$tosystemwallet->wallet_no, $tosystemwallet->keyword ?? '', 30]);

                                $tokenContract = $toWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $tosystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $tosystemwallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                $fromsystemwallet1->balance += $totalAmount;
                                $fromsystemwallet1->update();
                            }

                            if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code == 'BTC') {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tokenContract = $fromWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                $res = RPC_BTC_Send('sendtoaddress', [$toWallet->wallet_no, amount($finalAmount, 2)], $fromsystemwallet->keyword);
                                if (isset($res->error->message)){
                                    $to_message = "You can not exchange money because BTC has some issue :". $res->error->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }
                            if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code == 'ETH') {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tokenContract = $fromWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
                                $tx = '{"from": "' . $fromsystemwallet->wallet_no . '", "to": "' . $toWallet->wallet_no . '", "value": "0x' . dechex($finalAmount * pow(10, 18)) . '"}';
                                $res = RPC_ETH_Send('personal_sendTransaction', $tx, $fromsystemwallet->keyword ?? '');
                                if($res == 'error') {
                                    $to_message = "You can not exchange money because Ether has some issue.";
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }
                            }

                            if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->type == 1) {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tosystemwallet1 = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$tosystemwallet1) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tokenContract = $fromWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                $tosystemwallet1->balance -= $finalAmount;
                                $tosystemwallet1->update();
                            }

                            if ($fromWallet->currency->code != 'ETH' && $fromWallet->currency->code != 'BTC' && $fromWallet->currency->type == 2 && $toWallet->currency->code != 'ETH' && $toWallet->currency->code != 'BTC' && $toWallet->currency->type == 2) {
                                RPC_ETH('personal_unlockAccount', [$fromWallet->wallet_no, $fromWallet->keyword ?? '', 30]);
                                $tosystemwallet = get_wallet(0, $fromWallet->currency->id, 9);
                                if (!$tosystemwallet) {
                                    return back()->with('error', $fromWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $fromsystemwallet = get_wallet(0, $toWallet->currency->id, 9);
                                if (!$fromsystemwallet) {
                                    return back()->with('error', $toWallet->currency->code . ' System Account does not exist. you can not exchange now. Please contact to support team. ');
                                }
                                $tokenContract = $fromWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract, $fromWallet->wallet_no, $tosystemwallet->wallet_no, $totalAmount, $fromWallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;
                                }

                                RPC_ETH('personal_unlockAccount', [$fromsystemwallet->wallet_no, $fromsystemwallet->keyword ?? '', 30]);
                                $tokenContract = $toWallet->currency->address;
                                $result = erc20_token_transfer($tokenContract,$fromsystemwallet->wallet_no, $toWallet->wallet_no, $finalAmount, $fromsystemwallet->keyword);
                                if (json_decode($result)->code == 1){
                                    $to_message = "You can not exchange money because Ether has some issue :".json_decode($result)->message;
                                    $w_session->data = null;
                                    $w_session->save();
                                    send_message_telegram($to_message, $chat_id);
                                    return;

                                }
                            }

                            if ($fromWallet->currency->type == 1 && $toWallet->currency->type == 1) {
                                user_wallet_increment(0, $fromWallet->currency->id, $transaction_global_cost * $from_rate, 9);
                            }

                            $fromWallet->balance -= $totalAmount;

                            $fromWallet->update();

                            $toWallet->balance += $finalAmount;
                            $toWallet->update();

                            $exchange = new ExchangeMoney();
                            $exchange->trnx = str_rand();
                            $exchange->from_currency = $fromWallet->currency->id;
                            $exchange->to_currency = $toWallet->currency->id;
                            $exchange->from_wallet_id = $fromWallet->id;
                            $exchange->to_wallet_id = $toWallet->id;
                            $exchange->user_id = $user->id;
                            $exchange->charge = $charge + $transaction_custom_cost * $from_rate;;
                            $exchange->from_amount = $w_session->data->amount;
                            $exchange->to_amount = $finalAmount;
                            $exchange->save();


                            $w_session->data = null;
                            $w_session->save();
                            $to_message = $question['to_wallet_id'];

                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                        else {
                            $to_message = 'Please input number to select convert currency correctly.';
                            send_message_telegram($to_message, $chat_id);
                            return;
                        }
                    }

                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];
                }
                send_message_telegram($to_message, $chat_id);
            }
            else {
                switch ($text) {
                    case 'Balance':
                        $user = User::findOrFail($telegram_user->user_id);
                        $currency = Currency::findOrFail(defaultCurr());
                        $to_message = $currency->symbol.amount(userBalance($user->id), $currency->type, 2);
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'CryptoBalance':
                        $user = User::findOrFail($telegram_user->user_id);
                        $currencies =Currency::where('type', 2)->where('status', 1)->get();
                        $def_currency = Currency::findOrFail(defaultCurr());
                        $to_message = '';
                        foreach($currencies as $currency) {
                            $amount = amount(Crypto_Balance($user->id, $currency->id), 2);
                            $amount_fiat = amount(Crypto_Balance_Fiat($user->id, $currency->id), 1);
                            $amount = $amount.' ('.$amount_fiat.$def_currency->code.')';
                            $to_message = $to_message.$currency->code." : ".$amount."\n";
                        }
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'Logout':
                        $telegram = UserTelegram::where('chat_id', $chat_id)->first();
                        $telegram->status = 0;
                        $telegram->save();
                        $to_message = 'You have been log out successfully. ';
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'Beneficiary':
                        $to_message = "Please select Beneficiay Type: \nIndividual \ Corporate\n\nPlease type in # to go back to menu
                        ";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'Beneficiary';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'Beneficiary_Simple':
                        $to_message = "When you input, all data have to be splite by ;. Please Input to register beneficiary simple like this: \n{Individual\Corporate}; {FirstName LastName\CompanyName}; {Email}; {Phonenumber}; {Address}; {Registration NO}; {VAT NO}; {Contact Person}; {Bank IBAN}\n\n For example:\n Individual; John Doe; johndoe@gmail.com; +371 1111 1234; Riga Saulkaines bid 9; 11111111; 2222222; John Mark; MT08CFTE28000000000000000000000\n\n If you want to back, please type in # to go back to menu
                        ";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'Beneficiary_Simple';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'BankTransfer':
                        $beneficiary_list = Beneficiary::where('user_id',  $telegram_user->user_id)->get();
                        $beneficiaries = '';
                        foreach ($beneficiary_list as $key => $beneficiary) {
                            $beneficiaries = $beneficiaries.$beneficiary->id.':'.$beneficiary->name."\n";
                        }
                        if(strlen($beneficiaries) == 0) {
                            $to_message = "You have no registered beneficiary. Please register beneficiary.";
                            send_message_telegram($to_message, $chat_id);
                            break;
                        }
                        $to_message = "Please input number to select Beneficiary\n ".$beneficiaries."Please type in # to go back to menu
                        ";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'BankTransfer';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'InternalTransfer':
                        $to_message = "Please input sender email.\n Please type in # to go back to menu
                        ";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'InternalTransfer';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'CryptoWithdraw':
                        $wallet_list = Wallet::where('user_id',$telegram_user->user_id)->where('user_type',1)->where('wallet_type', 8)->with('currency')->get();
                        $user = User::findOrFail($telegram_user->user_id);

                        $currency = Currency::findOrFail(defaultCurr());
                        $userType = explode(',', $user->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array( '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                        $modules = explode(" , ", $user->modules);
                        if (in_array('Crypto',$modules)) {
                            $wallet_type_list['8'] = 'Crypto';
                        }
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', $telegram_user->user_id)->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }
                        $message_list = '';
                        foreach($wallet_list as $wallet) {
                            $amount = amount(Crypto_Balance($wallet->user_id, $wallet->currency_id), 2);
                            $amount_fiat = amount(Crypto_Balance_Fiat($wallet->user_id, $wallet->currency_id), 1);
                            $amount = $amount.' ('.$amount_fiat.$currency->code.')';
                            if ($amount > 0) {
                                $message_list = $message_list.$wallet->id.':'.$wallet->currency->code.' -- '.$amount.' -- '.$wallet_type_list[$wallet->wallet_type]."\n";
                            }
                        }
                        $to_message = "Please input number to select wallet.\n".$message_list."\n Please type in # to go back to menu
                        ";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'CryptoWithdraw';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'RequestMoney':
                        $to_message = "Please input email to request money";
                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'RequestMoney';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    case 'Exchange':
                        $user = User::findOrFail($telegram_user->user_id);

                        $userType = explode(',', $user->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                        $modules = explode(" , ", $user->modules);
                        if (in_array('Crypto',$modules)) {
                            $wallet_type_list['8'] = 'Crypto';
                        }
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', $telegram_user->user_id)->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }
                        $wallets = Wallet::where('user_id',$telegram_user->user_id)->with('currency')->get();
                        $wallet_list = '';
                        $currency = Currency::findOrFail(defaultCurr());
                        foreach ($wallets as $key => $wallet) {
                            if (isset($wallet_type_list[$wallet->wallet_type])){
                                if($wallet->currency->type == 2) {
                                    $amount = amount(Crypto_Balance($wallet->user_id, $wallet->currency_id), 2);
                                    $amount_fiat = amount(Crypto_Balance_Fiat($wallet->user_id, $wallet->currency_id), 1);
                                    $amount = $amount.' ('.$amount_fiat.$currency->code.')';

                                }
                                else {
                                    $amount = amount($wallet->balance,$wallet->currency->type,2);
                                }
                                if ($amount > 0) {
                                    $wallet_list = $wallet_list.$wallet->id.':'.$wallet->currency->code.' -- '.$amount.' -- '.$wallet_type_list[$wallet->wallet_type]."\n";
                                }
                            }
                        }
                        $to_message = "Please input number to select wallet\n".$wallet_list."\nPlease type in # to go back to menu";

                        $new_session = TelegramSession::where('user_id', $telegram_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new TelegramSession();
                        }
                        $new_session->user_id = $telegram_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'Exchange';
                        $new_session->save();
                        send_message_telegram($to_message, $chat_id);
                        break;
                    default:
                        # code...
                        $to_message = "Welcome to ".$gs->disqus."\nWhat could We help you?\nWe are here to help you with your problem.\nKindly choose an option to connect with our support team.\nCommand 1: Beneficiary\nCommand 2: BankTransfer\nCommand 3: Balance\nCommand 4: CryptoBalance\nCommand 5: Beneficiary_Simple\nCommand 6: InternalTransfer\nCommand 7: RequestMoney\nCommand 8: CryptoWithdraw\nCommand 9: Exchange\nCommand 10: Logout";
                        send_message_telegram($to_message, $chat_id);
                        break;
                }
            }
        }
        else if($telegram_user && $telegram_user->status == 1 && $telegram_user->user_id == 0) {
            switch ($text) {
                case 'Logout':
                    $telegram = UserTelegram::where('chat_id', $chat_id)->first();
                    $telegram->status = 0;
                    $telegram->save();
                    $to_message = 'You have been log out successfully. ';
                    send_message_telegram($to_message, $chat_id);
                    break;
                default:
                    # code...
                    $to_message = "Welcome to ".$gs->disqus."\nYou can receive staff message from our system.\nCommand 1: Logout";
                    send_message_telegram($to_message, $chat_id);
                    break;
            }
        }
        else{
            $text_split = explode(' ', $text);
            if($text_split[0] == 'CustomerLogin') {
                $text = 'CustomerLogin';
                $email = $text_split[1];
                $pincode = $text_split[2];
            }
            if($text_split[0] == 'StaffLogin') {
                $text = 'StaffLogin';
                $email = $text_split[1];
                $pincode = $text_split[2];
            }
            switch ($text) {
                case 'CustomerLogin':
                    $user = User::where('email', $email)->first();
                    if(!$user) {
                        send_message_telegram('This user dose not exist in our system', $chat_id);
                        break;
                    }
                    $telegram = UserTelegram::where('user_id', $user->id)->where('pincode', $pincode)->first();
                    if(!$telegram) {
                        send_message_telegram('Pincode is not matched with email. Please input again', $chat_id);
                        break;
                    }
                    if($telegram->status == 1) {
                        send_message_telegram('You are already login.', $chat_id);
                        break;
                    }
                    $telegram->chat_id = $chat_id;
                    $telegram->status = 1;
                    $telegram->save();
                    $to_message = "You login Successfully,\nPlease use follow command list:\nCommand 1: Beneficiary\nCommand 2: BankTransfer\nCommand 3: Balance\nCommand 4: CryptoBalance\nCommand 5: Beneficiary_Simple\nCommand 6: InternalTransfer\nCommand 7: RequestMoney\nCommand 8: CryptoWithdraw\nCommand 9: Exchange\nCommand 10: Logout";
                    send_message_telegram($to_message, $chat_id);
                    break;
                case 'StaffLogin':
                    $user = Admin::where('email', $email)->first();
                    if(!$user) {
                        send_message_telegram('This Staff dose not exist in our system', $chat_id);
                        break;
                    }
                    $telegram = UserTelegram::where('staff_id', $user->id)->where('pincode', $pincode)->first();
                    if(!$telegram) {
                        send_message_telegram('Pincode is not matched with email. Please input again', $chat_id);
                        break;
                    }
                    if($telegram->status == 1) {
                        send_message_telegram('You are already login.', $chat_id);
                        break;
                    }
                    $telegram->chat_id = $chat_id;
                    $telegram->status = 1;
                    $telegram->save();
                    $to_message = "You login Successfully,\nYou can receive staff message from our system.\nPlease use follow command list:\nCommand 1: Logout";
                    send_message_telegram($to_message, $chat_id);
                    break;
                default:
                    # code...
                    $to_message = "Welcome to ".$gs->disqus."\nWhat could We help you?\nWe are here to help you with your problem.\nKindly choose an option to connect with our support team.\nFirstly we have to login by using Login Command.\nCommand 1: CustomerLogin {email} {pincode}\nCommand 2: StaffLogin {email} {pincode}\nCommand 3: Help";
                    send_message_telegram($to_message, $chat_id);
                    break;
            }
        }

        Log::Info($data);

    }


    public function crypto_deposit_sms() {
        $wallet_list = Wallet::where('wallet_type', 8)->with('currency')->get();
        if(!empty($wallet_list)) {
            foreach ($wallet_list as $key => $wallet) {
                $user = User::findOrFail($wallet->user_id);
                $balance = round(Crypto_Balance($wallet->user_id, $wallet->currency_id), 10);
                info($balance);
                if($balance > $wallet->balance ) {
                    info($wallet->balance);
                    send_telegram($wallet->user_id, "Your ".$wallet->currency->code." wallet 's balance is updated .\n ".($balance-$wallet->balance).$wallet->currency->code." is incoming in your wallet. \n Please check your wallet. \n Your wallet address is ".$wallet->wallet_no);
                    send_whatsapp($wallet->user_id, "Your ".$wallet->currency->code." wallet 's balance is updated .\n ".($balance-$wallet->balance).$wallet->currency->code." is incoming in your wallet. \n Please check your wallet. \n Your wallet address is ".$wallet->wallet_no);
                }
                $u_wallet = Wallet::findOrFail($wallet->id);
                $u_wallet->balance = $balance;
                $u_wallet->save();
            }
        }
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $telegram = UserTelegram::where('user_id', $user->id)->first();
        if(!$telegram){
            $telegram = new UserTelegram();
        }
        $telegram->user_id = $user->id;
        $telegram->pincode = Str::random(8);
        $telegram->save();
        return redirect()->back()->with('message','PinCode is generated successfully.');
    }

    public function bot_login()
    {
        $user_email = request('email');
        $pincode = request('pincode');
        $user = User::where('email', $user_email)->first();
        if(!$user) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This user does not exist in our system.']);
        }
        $telegram = UserTelegram::where('user_id', $user->id)->where('pincode', $pincode)->first();
        if(!$telegram) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Pincode is not matched with email. Please input again']);
        }
        if($telegram->status == 1) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You are already login.']);
        }
        $chat_id = request('chat_id');
        $telegram->chat_id = $chat_id;
        $telegram->status = 1;
        $telegram->save();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been login successfully']);
    }

    public function bot_logout()
    {
        $chat_id = request('chat_id');
        $telegram = UserTelegram::where('chat_id', $chat_id)->first();
        if(!$telegram) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You did not login before to our system.']);
        }
        if($telegram->status == 0) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You are already logout.']);
        }
        $telegram->chat_id = $chat_id;
        $telegram->status = 0;
        $telegram->save();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been logout successfully']);
    }

}
