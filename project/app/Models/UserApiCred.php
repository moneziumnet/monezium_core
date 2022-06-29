<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserApiCred extends Model
{
    protected $fillable = ['user_id,access_key,mode'];
    use HasFactory;
}
