<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserWhatsapp;
use App\Models\BotWebhook;
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

        $text = $data['message']['content']['text'];

        $gs = Generalsetting::first();
        switch ($text) {
            case 'Help':
                $to_message = 'Welcome to '.$gs->disqus.'\n What could We help you?
                We are here to help you with your problem.\n
                Kindly choose an option to connect with our support team. \n
                Firstly we have to login by using Login Command.';
                $this->send_message($to_message, $data['from']['number']);
                $to_message = 'Command 1: Login {email} {pincode}\n
                            Command 2: Balance';
                $this->send_message($to_message, $data['from']['number']);
                break;
            default:
                # code...
                $to_message = 'Welcome to '.$gs->disqus.'\n What could We help you?
                We are here to help you with your problem.\n
                Kindly choose an option to connect with our support team. \n
                Firstly we have to login by using Login Command.';
                $this->send_message($to_message, $data['from']['number']);
                $to_message = 'Command 1: Login {email} {pincode}\n
                            Command 2: Balance';
                $this->send_message($to_message, $data['from']['number']);
                break;
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
        Log::Info(json_decode($request->getContent()));
    }

    public function send_message($message, $to_number)
    {
        $gs = Generalsetting::first();

        $url = "https://messages-sandbox.nexmo.com/v0.1/messages";
        $params = ["to" => ["type" => "whatsapp", "number" => $to_number],
            "from" => ["type" => "whatsapp", "number" => "14157386102"],
            "message" => [
                "content" => [
                    "type" => "text",
                    "text" => $message
                ]
            ]
        ];
        $headers = ["Authorization" => "Basic " . $gs->nexmo_key . ":" . $gs->nexmo_secret];
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
