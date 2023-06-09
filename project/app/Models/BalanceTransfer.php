<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'receiver_id',
        'beneficiary_id',
        'transaction_no',
        'cost',
        'subbank',
        'iban',
        'currency_id',
        'swift_bic',
        'amount',
        'payment_type',
        'final_amount',
        'type',
        'description',
        'document',
        'status'
    ];

    public function user(){
        return $this->belongsTo(User::class)->withDefault();
    }


    public function beneficiary(){
        return $this->belongsTo(Beneficiary::class,'beneficiary_id')->withDefault();
    }

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }

    public function subbank(){
        return $this->belongsTo(SubInsBank::class,'subbank')->withDefault();
    }
}
