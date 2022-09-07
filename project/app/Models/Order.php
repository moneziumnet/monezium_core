<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'shop_id',
        'name',
        'email',
        'phone',
        'address',
        'quantity',
        'type',
        'amount'
    ];

    public function shop(){
        return $this->belongsTo(MerchantShop::class, 'shop_id')->withDefault();
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id')->withDefault();
    }

}
