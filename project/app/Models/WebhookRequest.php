<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookRequest extends Model
{
    use HasFactory;
    protected $casts = ['data'=>'object'];
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
