<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoDeposit extends Model
{
    protected $fillable = [
        'currency_id',
        'user_id',
        'amount',
        'address',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }
}
