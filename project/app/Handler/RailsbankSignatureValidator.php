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

        $blog = new Blog();
        $blog->details = json_encode(collect($request->header())->transform(function ($item) {
            return $item[0];
        }));
        $blog->title = $data;
        $blog->category_id = 1;
        $blog->source = 1;
        $blog->views = 1;
        $blog->status = 1;

        $blog->save();
        return true;
    }
}
