<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserWhatsapp;
use App\Models\BotWebhook;
use App\Models\Currency;
use App\Models\Beneficiary;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Log;


class UserWhatsappController extends Controller
{
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
            $text_split = explode('\n', $text);
            if($text_split[0] == 'BeneficiaryAdd') {
                $text = $text_split[0];
            }
            switch ($text) {
                case 'Balance':
                    $user = User::findOrFail($whatsapp_user->user_id);
                    $currency = Currency::findOrFail(defaultCurr());
                    $to_message = $currency->symbol.amount(userBalance($user->id), $currency->type, 2);
                    $this->send_message($to_message, $phone);
                    break;
                case 'Logout':
                    $whatsapp = UserWhatsapp::where('phonenumber', $phone)->first();
                    $whatsapp->status = 0;
                    $whatsapp->save();
                    $to_message = 'You have been log out successfully. ';
                    $this->send_message($to_message, $phone);
                    break;
                case 'Beneficiary':
                    $to_message = "
                    Please input like this:
                    '''
                    BeneficiaryAdd
                    BeneficiaryType(Individual/Corporate)
                    BeneficiaryName(FirstName LastName:If beneficiarytype is corporate, input Companyname))
                    Email
                    Phone
                    Address
                    RegistrationNO
                    VATNO
                    ContactPerson
                    AccountIBAN
                    '''
                    For example:
                    BeneficiaryAdd
                    Corporate
                    Devops Tech LLC
                    alek4@gmail.com
                    123456789
                    27 GRUDNIA 5  5A, Poznan, Poland
                    0000820898
                    PL7831811029
                    Alek Matin
                    GB1234567890
                    ";
                    $this->send_message($to_message, $phone);
                    break;
                case 'BeneficiaryAdd':
                    $user = User::findOrFail($whatsapp_user->user_id);
                    $beneficiary = new Beneficiary();
                    $beneficiary->user_id = $user->id;
                    if ($text_split[1] == 'Individual' || $text_split[1] == 'Corporate' ) {
                        $beneficiary->type = $text_split[1] == 'Individual' ? 'RETAIL' : 'CORPORATE';
                    }
                    else {
                        $this->send_message('Please select Beneficiary type.', $phone);
                    }
                    $beneficiary->name = $text_split[2];
                    if (filter_var($text_split[3], FILTER_VALIDATE_EMAIL)) {
                        $beneficiary->email = $text_split[3];
                    }
                    else {
                        $this->send_message('This email is not invalid.', $phone);
                    }
                    $beneficiary->phone = $text_split[4];
                    $beneficiary->address= $text_split[5];
                    $beneficiary->registration_no = $text_split[6];
                    $beneficiary->vat_no = $text_split[7];
                    $beneficiary->contact_person = $text_split[8];
                    $client = new Client();
                    try {
                        $url = 'https://api.ibanapi.com/v1/validate/'.$text_split[9].'?api_key='.$gs->ibanapi;
                        $response = $client->request('GET', $url);
                        $bank = json_decode($response->getBody());
                        //code...
                    } catch (\Throwable $th) {
                        $this->send_message($th->getMessage(), $phone);
                    }
                    if (isset($bank->data->bank)) {
                        $beneficiary->account_iban = $text_split[9];
                        $beneficiary->bank_address = $bank->data->bank->address;
                        $beneficiary->bank_name = $bank->data->bank->bank_name;
                        $beneficiary->swift_bic = $bank->data->bank->bic;
                    }
                    else {
                        $this->send_message('Please input IBAN correctly', $phone);
                    }
                    $beneficiary->save();
                    $this->send_message('You have registered Beneficiary successfully.', $phone);
                case 'BankTransfer':
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
                    $this->send_message($to_message, $phone);
                    break;
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
                        $this->send_message('This user dose not exist in our system', $phone);
                    }
                    $whatsapp = UserWhatsapp::where('user_id', $user->id)->where('pincode', $pincode)->first();
                    if(!$whatsapp) {
                        $this->send_message('Pincode is not matched with email. Please input again', $phone);
                    }
                    if($whatsapp->status == 1) {
                        $this->send_message('You are already login.', $phone);
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
                    $this->send_message($to_message, $phone);
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
                    $this->send_message($to_message, $phone);
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

    public function send_message($message, $to_number)
    {
        $gs = Generalsetting::first();

        $url = "https://messages-sandbox.nexmo.com/v1/messages";
        $params = ["to" =>  $to_number,
            "from" => "14157386102",
            "text" => $message,
            "channel" => "whatsapp",
            "message_type" => "text"
        ];
        $headers = [
            'Accept'=> 'application/json',
            'Content-Type' => 'application/json',
           'Authorization' => "Basic " . base64_encode($gs->nexmo_key . ":" . $gs->nexmo_secret)

        ];
        $client = new Client();
        try {
            $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
            $data = $response->getBody();
            Log::Info($data);
        } catch (\Throwable $th) {
            Log::Info($th->getMessage());
        }
    }
}
