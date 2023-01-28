<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    protected $fillable = [
        'subject',
        'url',
        'ip',
        'agent',
        'user_id'
    ];


    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
