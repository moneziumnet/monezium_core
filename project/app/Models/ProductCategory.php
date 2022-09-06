<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'user_id',
        'name'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

}
