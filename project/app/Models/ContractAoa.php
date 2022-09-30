<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAoa extends Model
{
    protected $fillable = [
        'contract_id',
        'title',
        'information',
        'status',
        'contracter_image_path',
        'customer_image_path',
        'client_id',
        'contractor_id',
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
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id')->withDefault();

    }
}
