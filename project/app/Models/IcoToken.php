<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcoToken extends Model
{
    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault();
    }

}
