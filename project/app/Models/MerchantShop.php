<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantShop extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'merchant_id',
        'document',
        'url',
        'webhook',
        'site_key',
        'status',
    ];

}
