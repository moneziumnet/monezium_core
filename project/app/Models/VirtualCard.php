<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualCard extends Model
{
    protected $fillable = [ 'user_id',
    'first_name',
    'last_name',
    'account_id',
    'card_hash',
    'card_pan',
    'masked_card',
    'cvv',
    'expiration',
    'card_type',
    'name_on_card',
    'callback',
    'secret',
    'amount',
    'rate',
    'currency_id',
    'charge',
    'ref_id',
    'city',
    'state',
    'wallet_id',
    'address',
    'zip_code'];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->withDefault();
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id')->withDefault();
    }

}
