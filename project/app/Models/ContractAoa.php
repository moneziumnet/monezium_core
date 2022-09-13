<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAoa extends Model
{
    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'status',
        'contracter_image_path',
        'customer_image_path',
        'client_id',
        'contractor_id',
    ];
    public function beneficiary()
    {
        return $this->belongsTo(ContractBeneficiary::class, 'client_id')->withDefault();
    }

    public function contractor()
    {
        return $this->belongsTo(User::class, 'contractor_id')->withDefault();
    }
}
