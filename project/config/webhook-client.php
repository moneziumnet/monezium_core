<?php

return [
    'configs' => [
        [
            'name' => 'openpayd',
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),
            'signature_header_name' => 'signature',
            // \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'signature_validator' => App\Handler\OpenpaySignatureValidator::class,
            'webhook_profile' => App\Handler\CustomWebhookProfile::class,
            'webhook_response' => App\Handler\OpenpaydResponse::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => App\Handler\CustomProcessWebhook::class,
        ],
        [
            'name' => 'railsbank',
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),
            'signature_header_name' => 'signature',
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'webhook_profile' => App\Handler\CustomWebhookProfile::class,
            'webhook_response' => App\Handler\RailsbankResponse::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => App\Handler\CustomProcessWebhook::class,
        ],
    ],
];
