<?php
namespace App\Handler;

use App\Models\Currency;
use App\Models\WebhookRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class RailsbankResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());

        if(!isset($obj->transaction_id)) {
            return response()->json('error');
        }
        $webrequest = WebhookRequest::where('transaction_id', $obj->transaction_id)
            ->where('gateway_type', 'railsbank')
            ->first();        
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->transaction_id;
        switch($obj->type) {
            case "transaction-pending":
                $webrequest->status = "processing";
                break;
            case "transaction-accepted":
                $webrequest->status = "completed";
                break;
            case "transaction-declined":
                $webrequest->status = "failed";
                break;
            default:
                return response()->json('error');
        }
        $webrequest->gateway_type = "railsbank";

        $client = new Client();
        $response = $client->request('get', 'https://play.railsbank.com/v1/customer/transactions/'.$obj->transaction_id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'API-Key x9zx3980azu8edv2h9sve52kyog6c8g3#t4ugygi8yogyat4dlgu9a9jye4jbhx2ggz68yj4a4e49xqdu7d38erc2128yoqdq',
            ],
        ]);
        $details = json_decode($response->getBody());

        $webrequest->sender_name = $details->transaction_info->sender->name;
        
        $address = $details->transaction_printout->pspofsenderaddress;
        $webrequest->sender_address = $address->address_street.', '.$address->address_city.', '.$address->address_iso_country;

        $currency = Currency::where('code', $details->transaction_info->currency)->first();
        $webrequest->currency_id = $currency ? $currency->id : 0;
        $webrequest->amount = $details->amount;
        $webrequest->reference = $details->reference;

        $webrequest->save();

        return response()->json("success");
    }
}
