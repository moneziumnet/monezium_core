<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoCurrency extends Model
{
    protected $fillable = [ 'curr_name','code','symbol','rate','status'];
    public $timestamps = false;

}
