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
        $obj = str2obj($request->getContent());

        $webrequest = WebhookRequest::where('transaction_id', $obj->transaction_id)
            ->where('gateway_type', 'railsbank')
            ->first();        
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->transaction_id;
        switch($obj->type) {
            case "transaction-pending":
                $webrequest->status = "processing";
            case "transaction-accepted":
                $webrequest->status = "completed";
            case "transaction-declined":
                $webrequest->status = "failed";
        }
        $webrequest->gateway_type = "railsbank";

        $client = new Client();
        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions/'.$obj->transaction_id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
        $details = json_decode($response->getBody());

        $webrequest->sender_name = $details->transaction_info->sender->name;
        
        $address = $details->transaction_info->sender->address;
        $webrequest->sender_address = $address->lines.", ".$address->country;

        $currency = Currency::where('code', $details->transaction_currency)->first();
        $webrequest->currency_id = $currency ? $currency->id : 0;
        $webrequest->amount = $details->amount;
        $webrequest->reference = $details->reference;
        $webrequest->failure_reason = count($details->failure_reasons) > 0 ? $details->failure_reasons[0] : null;

        $webrequest->save();

        return response()->json("success");
    }
}
