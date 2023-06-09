<?php
namespace App\Handler;

use \Spatie\WebhookClient\Jobs\ProcessWebhookJob;

//The class extends "ProcessWebhookJob" class as that is the class //that will handle the job of processing our webhook before we have //access to it.
class CustomProcessWebhook extends ProcessWebhookJob
{
    public function handle()
    {
        $data = json_decode($this->webhookCall, true);
        //Do something with the event
        logger($data['payload']);
        http_response_code(200); //Acknowledge you received the response
    }
}
