<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawals extends Model
{
    use HasFactory;

    public function method()
    {
        return $this->belongsTo(PaymentGateway::class,'method_id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class,'merchant_id');
    }
}
