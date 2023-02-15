<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserWhatsapp;
use App\Models\BotWebhook;
use App\Models\Currency;
use App\Models\WhatsappSession;
use App\Models\Beneficiary;
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
            if ($w_session != null) {
                $final = (array_key_last(((array)$w_session->data)));
                if($final == null) {
                    if ( $text == 'Individual' || $text == 'Corporate') {
                        $question = $text == 'Individual' ? $this->beneficiary_json : $this->beneficiary_company_json;
                        $to_message = $question['type'];
                        $dump = $w_session->data;
                        $dump->type = $text == 'Individual' ? 'RETAIL' : 'CORPORATE';;
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
                            $input = json_decode(json_encode($w_session), true);;

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
                        $to_message = "Please select Beneficiay Type:
                        Individual \ Corporate
                        ";
                        $new_session = new WhatsappSession();
                        $new_session->user_id = $whatsapp_user->user_id;
                        $new_session->data = json_decode('{}');
                        $new_session->type = 'Beneficiary';
                        $new_session->save();
                        send_message_whatsapp($to_message, $phone);
                        break;
                    // case 'BeneficiaryAdd':
                    //     $user = User::findOrFail($whatsapp_user->user_id);
                    //     $beneficiary = new Beneficiary();
                    //     $beneficiary->user_id = $user->id;
                    //     if ($text_split[1] == 'Individual' || $text_split[1] == 'Corporate' ) {
                    //         $beneficiary->type = $text_split[1] == 'Individual' ? 'RETAIL' : 'CORPORATE';
                    //     }
                    //     else {
                    //         send_message_whatsapp('Please select Beneficiary type.', $phone);
                    //         break;
                    //     }
                    //     $beneficiary->name = $text_split[2];
                    //     if (filter_var($text_split[3], FILTER_VALIDATE_EMAIL)) {
                    //         $beneficiary->email = $text_split[3];
                    //     }
                    //     else {
                    //         send_message_whatsapp('This email is not invalid.', $phone);
                    //         break;
                    //     }
                    //     $beneficiary->phone = $text_split[4];
                    //     $beneficiary->address= $text_split[5];
                    //     $beneficiary->registration_no = $text_split[6];
                    //     $beneficiary->vat_no = $text_split[7];
                    //     $beneficiary->contact_person = $text_split[8];
                    //     $client = new Client();
                    //     try {
                    //         $url = 'https://api.ibanapi.com/v1/validate/'.$text_split[9].'?api_key='.$gs->ibanapi;
                    //         $response = $client->request('GET', $url);
                    //         $bank = json_decode($response->getBody());
                    //         //code...
                    //     } catch (\Throwable $th) {
                    //         send_message_whatsapp(explode('response:', $th->getMessage())[1], $phone);
                    //         break;
                    //     }
                    //     if (isset($bank->data->bank)) {
                    //         $beneficiary->account_iban = $text_split[9];
                    //         $beneficiary->bank_address = $bank->data->bank->address;
                    //         $beneficiary->bank_name = $bank->data->bank->bank_name;
                    //         $beneficiary->swift_bic = $bank->data->bank->bic;
                    //     }
                    //     else {
                    //         send_message_whatsapp('Please input IBAN correctly', $phone);
                    //         break;
                    //     }
                    //     $beneficiary->save();
                    //     send_message_whatsapp('You have registered Beneficiary successfully.', $phone);
                    //     break;
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
