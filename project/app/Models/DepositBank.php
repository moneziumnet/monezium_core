<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositBank extends Model
{
    protected $fillable = [
        'user_id',
        'deposit_number',
        'amount',
        'currency_id',
        'txnid',
        'method',
        'charge_id',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class)->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }
}
