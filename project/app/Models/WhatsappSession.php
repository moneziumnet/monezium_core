<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'data',
        'type',
    ];
    protected $casts = ['data'=>'object'];


    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
