<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserWhatsapp;
use App\Models\BotWebhook;
use App\Models\Currency;
use App\Models\WhatsappSession;
use App\Models\Beneficiary;
use App\Models\SubInsBank;
use App\Models\BankAccount;
use App\Models\BankPoolAccount;
use App\Models\BankPlan;
use App\Models\PlanDetail;
use App\Models\BalanceTransfer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Log;


class UserWhatsappController extends Controller
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

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['inbound', 'status']]);
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $whatsapp = UserWhatsapp::where('user_id', $user->id)->first();
        if(!$whatsapp){
            $whatsapp = new UserWhatsapp();
        }
        $whatsapp->user_id = $user->id;
        $whatsapp->pincode = Str::random(8);
        $whatsapp->save();
        return redirect()->back()->with('message','PinCode is generated successfully.');
    }

    public function test() {
        $w_session = WhatsappSession::where('user_id', auth()->id())->first();
        if (!$w_session) {
            $w_session = new WhatsappSession();
            $w_session->user_id = auth()->id();
            $w_session->data = json_decode('{}');
            $w_session->type = 'Beneficiary';
            $w_session->save();
        }
        $final = (array_key_last(((array)$w_session->data)));
        dd($this->beneficiary_json['type']);
        dd($w_session->data->$final);
    }

    public function inbound(Request $request)
    {
        $data = $request->all();
        $whatsapp_hook = new BotWebhook();
        $whatsapp_hook->name = 'whastapp';
        $whatsapp_hook->payload = json_decode($request->getContent());
        $whatsapp_hook->url = route('user.whatsapp.inbound');
        $whatsapp_hook->save();

        $gs = Generalsetting::first();
        $text = $data['text'];

        $whatsapp_user = UserWhatsapp::where('phonenumber', $data['from'])->first();
        $phone = $data['from'];
        if($whatsapp_user && $whatsapp_user->status == 1) {
            $w_session = WhatsappSession::where('user_id', $whatsapp_user->user_id)->first();
            if ($w_session != null && $w_session->data != null && $w_session->type == "Beneficiary") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from beneficiary register successfully. ";
                    send_message_whatsapp($to_message, $phone);
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
                            send_message_whatsapp($to_message, $phone);
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
                        } catch (\Throwable $th) {
                            send_message_whatsapp(explode('response:', $th->getMessage())[1]."\n Please input IBAN correctly.", $phone);
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

                            send_message_whatsapp('You completed beneficiary register successfully.', $phone);
                            return;
                        }
                        else {
                            send_message_whatsapp('Please input IBAN correctly', $phone);
                            return;
                        }
                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];
                }
                send_message_whatsapp($to_message, $phone);
            }
            elseif($w_session != null && $w_session->data != null && $w_session->type == "BankTransfer") {
                if($text == '#') {
                    $w_session->data = null;
                    $w_session->save();
                    $to_message = "You exit from Bank Transfer successfully. ";
                    send_message_whatsapp($to_message, $phone);
                    return;
                }
                $final = (array_key_last(((array)$w_session->data)));
                if($final == null) {
                    $ids = Beneficiary::where('user_id',  $whatsapp_user->user_id)->pluck('id')->toArray();

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
                            send_message_whatsapp($to_message, $phone);
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
                                $currencies = BankAccount::whereUserId($whatsapp_user->user_id)->where('subbank_id', $text)->with('currency')->get();
                            } else {
                                $currencies = BankPoolAccount::where('bank_id', $text)->with('currency')->get();
                            }
                            $currency_list = '';
                            foreach ($currencies as $key => $currency) {
                                $currency_list = $currency_list.$currency->currency->id.':'.$currency->currency->code;
                            }
                            if(strlen($currency_list) == 0) {
                                $to_message = "You have no registered Bank Account. Please create bank account in Web Dashboard.";
                                $w_session->data = null;
                                $w_session->save();
                                send_message_whatsapp($to_message, $phone);
                                return;
                            }

                            $to_message = $question['subbank']."\n".$currency_list;
                            $dump = $w_session->data;
                            $dump->subbank = $text;
                            $w_session->data = $dump;
                            $w_session->save();
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                        else {
                            $to_message = "Please input number to select Bank account correctly.";
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                    }
                    if($next_key == "currency_id") {
                        $subbank = SubInsBank::find($w_session->data->subbank);
                        if($subbank->hasGateway()){
                            $currencies = BankAccount::whereUserId($whatsapp_user->user_id)->where('subbank_id', $w_session->data->subbank)->with('currency')->get();
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
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                        else {
                            $to_message = "Please input number to select currency correctly.";
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                    }
                    if($next_key == "amount") {
                        if (!is_numeric($text)) {
                            $to_message = "Please input number for amount correctly.";
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                        if ($text >= $gs->other_bank_limit) {
                            $to_message = "Please input less amount than ".$gs->other_bank_limit;
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                        $user = User::findOrFail($whatsapp_user->user_id);

                        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
                        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
                        $dailySend = BalanceTransfer::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
                        $monthlySend = BalanceTransfer::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

                        if($dailySend > $global_range->daily_limit){
                            $to_message = "Daily send limit over.";
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }

                        if($monthlySend > $global_range->monthly_limit){
                            $to_message = "Monthly send limit over.";
                            send_message_whatsapp($to_message, $phone);
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
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }

                        if($global_range->max < $text/$rate){
                            $to_message = 'Request Amount should be less than this '.$global_range->max;
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }

                        $balance = user_wallet_balance($user->id, $w_session->data->currency_id);

                        if($balance < 0 || $finalAmount > $balance){
                            $to_message = 'Insufficient Balance!';
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }

                        if($global_range->daily_limit <= $finalAmount){
                            $to_message = 'Your daily limitation of transaction is over.';
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }

                        if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                            $to_message = 'Your daily limitation of transaction is over.';
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }


                        if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                            $to_message = 'Your monthly limitation of transaction is over.';
                            send_message_whatsapp($to_message, $phone);
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
                        send_message_whatsapp($to_message.$extra_string, $phone);
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
                            send_message_whatsapp($to_message, $phone);
                            return;
                        }
                        else {
                            $to_message = "Please select payment type correctly.";
                            send_message_whatsapp($to_message, $phone);
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
                        } catch (\Throwable $th) {
                            send_message_whatsapp(explode('response:', $th->getMessage())[1]."\n Please input IBAN correctly.", $phone);
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

                            send_message_whatsapp('You completed beneficiary register successfully.', $phone);
                            return;
                        }
                        else {
                            send_message_whatsapp('Please input IBAN correctly', $phone);
                            return;
                        }
                    }
                    $dump = $w_session->data;
                    $dump->$next_key = $text;
                    $w_session->data = $dump;
                    $w_session->save();
                    $to_message = $question[$next_key];

                }
                send_message_whatsapp($to_message, $phone);
            }
            else {
                switch ($text) {
                    case 'Balance':
                        $user = User::findOrFail($whatsapp_user->user_id);
                        $currency = Currency::findOrFail(defaultCurr());
                        $to_message = $currency->symbol.amount(userBalance($user->id), $currency->type, 2);
                        send_message_whatsapp($to_message, $phone);
                        break;
                    case 'Logout':
                        $whatsapp = UserWhatsapp::where('phonenumber', $phone)->first();
                        $whatsapp->status = 0;
                        $whatsapp->save();
                        $to_message = 'You have been log out successfully. ';
                        send_message_whatsapp($to_message, $phone);
                        break;
                    case 'Beneficiary':
                        $to_message = "Please select Beneficiay Type: \nIndividual \ Corporate\n\nPlease type in # to go back to menu
                        ";
                        $new_session = WhatsappSession::where('user_id', $whatsapp_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new WhatsappSession();
                        }
                        $new_session->user_id = $whatsapp_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'Beneficiary';
                        $new_session->save();
                        send_message_whatsapp($to_message, $phone);
                        break;
                    case 'BankTransfer':
                        $beneficiary_list = Beneficiary::where('user_id',  $whatsapp_user->user_id)->get();
                        $beneficiaries = '';
                        foreach ($beneficiary_list as $key => $beneficiary) {
                            $beneficiaries = $beneficiaries.$beneficiary->id.':'.$beneficiary->name."\n";
                        }
                        if(strlen($beneficiaries) == 0) {
                            $to_message = "You have no registered beneficiary. Please register beneficiary.";
                            send_message_whatsapp($to_message, $phone);
                            break;
                        }
                        $to_message = "Please input number to select Beneficiary\n ".$beneficiaries."Please type in # to go back to menu
                        ";
                        $new_session = WhatsappSession::where('user_id', $whatsapp_user->user_id)->first();
                        if(!$new_session) {
                            $new_session = new WhatsappSession();
                        }
                        $new_session->user_id = $whatsapp_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'BankTransfer';
                        $new_session->save();
                        send_message_whatsapp($to_message, $phone);
                        break;
                    default:
                        # code...
                        $to_message = 'Welcome to '.$gs->disqus.'
                        What could We help you?
                        We are here to help you with your problem.
                        Kindly choose an option to connect with our support team.
                        Command 1: Beneficiary
                        Command 2: BankTransfer
                        Command 3: Balance
                        Command 4: Logout';
                        send_message_whatsapp($to_message, $phone);
                        break;
                }
            }
        }
        else{
            $text_split = explode(' ', $text);
            if($text_split[0] == 'Login') {
                $text = 'Login';
                $email = $text_split[1];
                $pincode = $text_split[2];
            }
            switch ($text) {
                case 'Login':
                    $user = User::where('email', $email)->first();
                    if(!$user) {
                        send_message_whatsapp('This user dose not exist in our system', $phone);
                    }
                    $whatsapp = UserWhatsapp::where('user_id', $user->id)->where('pincode', $pincode)->first();
                    if(!$whatsapp) {
                        send_message_whatsapp('Pincode is not matched with email. Please input again', $phone);
                    }
                    if($whatsapp->status == 1) {
                        send_message_whatsapp('You are already login.', $phone);
                    }
                    $whatsapp->phonenumber = $phone;
                    $whatsapp->status = 1;
                    $whatsapp->save();
                    $to_message = 'You login Successfully,
                                Please use follow command list:
                                Command 1: Beneficiary
                                Command 2: BankTransfer
                                Command 3: Balance
                                Command 4: Logout';
                    send_message_whatsapp($to_message, $phone);
                    break;
                default:
                    # code...
                    $to_message = 'Welcome to '.$gs->disqus.'
                    What could We help you?
                    We are here to help you with your problem.
                    Kindly choose an option to connect with our support team.
                    Firstly we have to login by using Login Command.
                    Command 1: Login {email} {pincode}
                    Command 2: Help';
                    send_message_whatsapp($to_message, $phone);
                    break;
            }
        }

        Log::Info($data);
    }

    public function status(Request $request)
    {
        $whatsapp_hook = new BotWebhook();
        $whatsapp_hook->name = 'whastapp';
        $whatsapp_hook->payload = json_decode($request->getContent());
        $whatsapp_hook->url = route('user.whatsapp.status');
        $whatsapp_hook->save();
        Log::Info($request->getContent());
    }

}
