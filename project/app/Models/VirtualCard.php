<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualCard extends Model
{
    use HasFactory;
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
    'address',
    'zip_code'];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->withDefault();
    }

}
