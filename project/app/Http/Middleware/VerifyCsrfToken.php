<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/paytm-callback',
        '/razorpay-notify',
        '/flutter/notify',
        '/coingate/notify',
        '/user/deposit/paytm-callback',
        '/user/deposit/razorpay-notify',
        '/blockio/notify',
        '/user/deposit/flutter/notify*',
        '/user/subscription/paytm-callback',
        '/user/subscription/razorpay-notify',
        '/user/subscription/flutter/notify*',
        '/user/globalpass/callback',
        'webhook-openpayd',
        'webhook-openpayd-uk',
        'webhook-railsbank',
        'webhook-swan',
        '/cj-payin',
        '/cj-payout',
        '/iban-create-completed',
        '/iban-inbound-settled',
        '/iban-outbound-settled',
        '/iban-outbound-rejected',
        '/whatsapp/inbound',
        '/whatsapp/status',
        '/telegram/inbound'
    ];
}
