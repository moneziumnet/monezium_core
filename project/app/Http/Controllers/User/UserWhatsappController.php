<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserTelegram;
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

    public function inbound(Request $request)
    {
        $data = $request->all();
        $whatsapp_hook = new BotWebhook();
        $whatsapp_hook->name = 'whastapp';
        $whatsapp_hook->payload = json_decode($request->getContent());
        $whatsapp_hook->url = route('user.whatsapp.inbound');
        $whatsapp_hook->save();

        $text = $data['message']['content']['text'];
        $number = intval($text);
        Log::Info($number);
        if($number > 0) {
            $random = rand(1, 8);
            Log::Info($random);
            $respond_number = $number * $random;
            Log::Info($respond_number);
            $url = "https://messages-sandbox.nexmo.com/v0.1/messages";
            $params = ["to" => ["type" => "whatsapp", "number" => $data['from']['number']],
                "from" => ["type" => "whatsapp", "number" => "14157386102"],
                "message" => [
                    "content" => [
                        "type" => "text",
                        "text" => "The answer is " . $respond_number . ", we multiplied by " . $random . "."
                    ]
                ]
            ];
            $headers = ["Authorization" => "Basic " . $gs->nexmo_key . ":" . $gs->nexmo_secret];

            $client = new Client();
            $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
            $data = $response->getBody();
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
