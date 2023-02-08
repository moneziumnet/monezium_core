<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTelegram extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pincode',
        'chat_id',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
