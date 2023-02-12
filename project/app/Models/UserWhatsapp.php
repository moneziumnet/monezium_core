<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWhatsapp extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pincode',
        'phonenumber',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
