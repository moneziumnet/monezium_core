<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'address',
        'bank_address',
        'swift_bic',
        'account_iban',
        'name',
        'email',
        'phone',
        'registration_no',
        'vat_no',
        'contact_person',
        'type'
    ];

    // protected $casts = [
    //     'name' => 'encrypted',
    //     'address' => 'encrypted',
    //     'bank_address' => 'encrypted',
    //     'swift_bic' => 'encrypted',
    //     'account_iban' => 'encrypted',
    //     'details' => 'encrypted',
    // ];


    public function transfers(){
        return $this->hasMany(BalanceTransfer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

}
