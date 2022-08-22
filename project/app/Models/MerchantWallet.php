<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantWallet extends Model
{
    use HasFactory;
    protected $fillable = ['merchant_id','currency_id','balance', 'wallet_no', 'shop_id'];

    public function merchant()
    {
        return $this->belongsTo(User::class,'merchant_id')->withDefault();
    }

    public function shop()
    {
        return $this->belongsTo(MerchantShop::class,'shop_id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::updated(function($wallet)
        {

        });
    }
}
