<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'contracter_image_path',
        'customer_image_path',
        'client_id',
        'contractor_id',
        'status',
    ];
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id')->withDefault();
    }

    public function contractor()
    {
        return $this->belongsTo(ContractBeneficiary::class, 'contractor_id')->withDefault();
    }

}
