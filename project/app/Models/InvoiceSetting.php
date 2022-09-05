<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $casts = ['number_generator' => 'object'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
