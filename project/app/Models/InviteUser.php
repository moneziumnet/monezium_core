<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invited_to',
        'invite_type',
        'status'
    ];

}
