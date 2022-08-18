<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAoa extends Model
{
    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'amount',
        'status',
    ];

}
