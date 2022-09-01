<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantCheckout extends Model
{
    protected $fillable = [
        'user_id',
        'ref_id',
        'amount',
        'currency_id',
        'name',
        'description',
        'shop_id',
        'redirect_link',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->withDefault();
    }

    public function shop()
    {
        return $this->belongsTo(MerchantShop::class,'shop_id')->withDefault();
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class)->withDefault();
    }
}
