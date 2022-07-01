<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubInsBank extends Model
{
    use HasFactory;
    protected $fillable = [
        'ins_id',
        'name',
        'address',
        'swift',
        'iban',
        'min_limit',
        'max_limit',
        'daily_maximum_limit',
        'monthly_maximum_limit',
        'monthly_total_transaction',
        'daily_total_transaction',
        'fixed_charge',
        'percent_charge',
        'required_information',
        'status',
    ];

    public function transfers(){
        return $this->hasMany(BalanceTransfer::class);
    }
}
