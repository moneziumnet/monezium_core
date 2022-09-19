<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class MerchantSetting extends Model
{
    protected $casts = [
        'information' => AsArrayObject::class,
    ];
}
