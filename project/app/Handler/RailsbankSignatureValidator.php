<?php
namespace App\Handler;

use App\Models\Blog;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class RailsbankSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $data = $request->getContent();
        $signature = $request->header($config->signatureHeaderName);

        $blog = new Blog();
        $blog->details = $data;
        $blog->title = $signature;
        $blog->category_id = 1;
        $blog->source = 1;
        $blog->view = 1;
        $blog->status = 1;

        $blog->save();
        return true;
    }
}
