<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{

    public function manager(){
        return $this->belongsTo(User::class, 'manager_id')->withDefault();
    }

    public function supervisor(){
        return $this->belongsTo(User::class, 'supervisor_id')->withDefault();
    }

}
