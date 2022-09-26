<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'information',
        'contracter_image_path',
        'customer_image_path',
        'client_id',
        'contractor_id',
        'contractor_type',
        'status',
    ];
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'client_id')->withDefault();
    }

    public function contractor()
    {
        // return $this->belongsTo(User::class, 'contractor_id')->withDefault();
        return $this->morphTo();
    }

    public function contract_aoa()
    {
        return $this->hasMany(ContractAoa::class);
    }

}
