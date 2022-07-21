<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','user_type','currency_id','balance', 'wallet_no', 'wallet_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::updated(function($wallet)
        {
            
        });
    }
}
