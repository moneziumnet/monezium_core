<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'subbank_id',
        'iban',
        'swift',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function subbank()
    {
        return $this->belongsTo(SubInsBank::class,'subbank_id')->withDefault();
    }
}
