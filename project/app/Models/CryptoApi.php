<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoApi extends Model
{
    protected $fillable = [
        'api_key',
        'api_secret',
        'withdraw_eth',
        'withdraw_btc',
        'keyword',
    ];

}
