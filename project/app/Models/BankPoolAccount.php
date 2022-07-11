<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankPoolAccount extends Model
{
    use HasFactory;
    protected $fillable = ['bank_id','currency_id','balance'];

    public function bank()
    {
        return $this->belongsTo(SubInsBank::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
