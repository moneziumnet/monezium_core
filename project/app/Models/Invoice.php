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
        return $this->belongsTo(InvoiceBeneficiary::class, 'beneficiary_id')->withDefault();
    }

    public function items()
    {
        return $this->hasMany(InvItem::class);
    }
}
