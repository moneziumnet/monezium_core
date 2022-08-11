<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubInsBank extends Model
{
    use HasFactory;
    protected $fillable = [
        'ins_id',
        'name',
        'address',
        'status',
    ];

    public function transfers(){
        return $this->hasMany(BalanceTransfer::class);
    }
}
