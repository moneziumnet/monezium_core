<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'cat_id',
        'name',
        'description',
        'currency_id',
        'amount',
        'quantity',
        'ref_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function shop(){
        return $this->belongsTo(MerchantShop::class, 'shop_id')->withDefault();
    }

    public function category(){
        return $this->belongsTo(ProductCategory::class, 'cat_id')->withDefault();
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault();
    }

}
