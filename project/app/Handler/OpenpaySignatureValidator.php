<?php
namespace App\Handler;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class OpenpaySignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $data = $request->getContent();

        $signature = $request->header($config->signatureHeaderName);
        if (!$signature) {
            return false;
        }
        
        $publicKeyPath = 'http://lion.saas.test/genius/assets/test.pem';
        $public_key = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        $return = openssl_verify($data, base64_decode($signature), $public_key, OPENSSL_ALGO_SHA256);
        return $return == 1;
    }
}
