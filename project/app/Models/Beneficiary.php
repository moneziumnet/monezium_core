<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'other_bank_id',
        'beneficiary_name',
        'address',
        'bank_address',
        'swift_bic',
        'account_iban',
        'details'
    ];

    public function bank(){
        return $this->belongsTo('App\Models\OtherBank','other_bank_id')->withDefault();
    }

    public function transfers(){
        return $this->hasMany(BalanceTransfer::class);
    }

}
