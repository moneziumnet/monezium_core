<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [ 'curr_name','code','symbol','rate','type','default','status'];
    public $timestamps = false;

    public function wiretransfers(){
        return $this->hasMany(WireTransferBank::class);
    }
}
