<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantShop extends Model
{
    protected $fillable = [
        'name',
        'merchant_id',
        'document',
        'url',
        'status',
    ];

}
