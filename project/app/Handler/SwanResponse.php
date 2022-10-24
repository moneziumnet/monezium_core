<?php
namespace App\Handler;

use App\Models\Currency;
use App\Models\WebhookRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class SwanResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());
        
        if(!isset($obj->resourceId)) {
            return response()->json('error');
        }
        $webrequest = WebhookRequest::where('transaction_id', $obj->resourceId)
            ->where('gateway_type', 'swan')
            ->first();    

        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->resourceId;
        switch($obj->eventType) {
            case "transaction-pending":
                $webrequest->status = "processing";
                break;
            case "Transaction.Booked":
                $webrequest->status = "completed";
                break;
            case "Transaction.Canceled":
            case "Transaction.Deleted":
            case "Transaction.Rejected":
                $webrequest->status = "failed";
                break;
            default:
                return response()->json('error');
        }

        $webrequest->gateway_type = "swan";
        
        $client = New Client();
        try {
            $options = [
                'multipart' => [
                [
                    'name' => 'client_id',
                    'contents' => "SANDBOX_b0749ed1-b2e6-4eaf-ae5c-a5c6854cd600"
                ],
                [
                    'name' => 'client_secret',
                    'contents' => "n0w778x7a581g67"
                ],
                [
                    'name' => 'grant_type',
                    'contents' => 'client_credentials'
                ]
            ]];
            $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
            $res_body = json_decode($response->getBody());
            $access_token = $res_body->access_token;
        } catch (\Throwable $th) {
            return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
        }
        try {
            $body = '{"query":"query Transaction($id: ID!) {\\n  transaction(id: $id) {\\n    id\\n    reference\\n    counterparty\\n    amount {\\n      currency\\n      value\\n    }\\n  }\\n}","variables":{"id": "'.$obj->resourceId.'"}}';
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
                'Content-Type' => 'application/json'
                ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $details = json_decode($response->getBody())->data->transaction;

            $webrequest->sender_name = $details->counterparty;
            $webrequest->sender_address = " ";
            $webrequest->reference = $details->reference;
            $webrequest->amount = $details->amount->value;
            $currency = Currency::where('code', $details->amount->currency)->first();
            $webrequest->currency_id = $currency ? $currency->id : 0;

            $webrequest->save();
            
        } catch (\Throwable $th) {
            return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
        }
        return response()->json("success");
    }
}