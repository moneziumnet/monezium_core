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
        'status',
        'min_limit',
        'max_limit',
        'fixed_charge',
        'percent_charge',
    ];

    public function transfers(){
        return $this->hasMany(BalanceTransfer::class);
    }

    public function subInstitution() {
        return $this->belongsTo(Admin::class, 'ins_id');
    }

    public function hasGateway(){
        $gateway = BankGateway::where('subbank_id', $this->id)->first();
        return isset($gateway);
    }
}
