<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public function currency()
    {
        return $this->belongsTo(Currency::class)->withDefault();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function beneficiary()
    {
        return $this->belongsTo(ContractBeneficiary::class, 'beneficiary_id')->withDefault();
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id')->withDefault();
    }

    public function aoa()
    {
        return $this->belongsTo(ContractAoa::class, 'contract_aoa_id')->withDefault();
    }

    public function items()
    {
        return $this->hasMany(InvItem::class);
    }
}
