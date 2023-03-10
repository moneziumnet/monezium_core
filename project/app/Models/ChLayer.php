<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChLayer extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','layer_id','pincode'];

}
