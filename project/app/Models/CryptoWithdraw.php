<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoWithdraw extends Model
{
    protected $fillable = [
        'currency_id',
        'user_id',
        'amount',
        'hash',
        'status'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }
}
