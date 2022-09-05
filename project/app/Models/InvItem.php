<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvItem extends Model
{
    use HasFactory;
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id')->withDefault();
    }
}
