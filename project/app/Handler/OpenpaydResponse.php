<?php
namespace App\Handler;

use App\Models\Currency;
use App\Models\WebhookRequest;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class OpenpaydResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());
        if(!$obj->transactionReference)
            return response()->json("failure");

        $currency = Currency::where('code', $obj->amount->currency)->first();
        $webrequest = WebhookRequest::where('reference', $obj->transactionReference)
            ->where('gateway_type', 'openpayd')
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->transactionId;
        $webrequest->sender_name = $obj->senderName;
        $webrequest->sender_address = $obj->senderAddress;
        $webrequest->amount = $obj->amount->value;
        $webrequest->currency_id = $currency ? $currency->id : 0;
        $webrequest->status = strtolower($obj->status);
        $webrequest->reference = $obj->transactionReference;
        $webrequest->failure_reason = $obj->failureReason??"";
        $webrequest->gateway_type = "openpayd";

        $webrequest->save();

        return response()->json("success");
    }
}
