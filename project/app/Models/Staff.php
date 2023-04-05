<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens;

   protected $fillable = ['name', 'email', 'password', 'status'];
   protected $table = 'staffs';

    protected $hidden = [
        'password', 'remember_token'
    ];

}
