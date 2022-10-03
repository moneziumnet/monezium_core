<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id')->withDefault();
    }
}
