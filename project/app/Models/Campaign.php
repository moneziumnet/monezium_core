<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'cat_id',
        'title',
        'goal',
        'currency_id',
        'deadline',
        'description',
        'logo',
        'status',
        'ref_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function category(){
        return $this->belongsTo(CampaignCategory::class, 'cat_id')->withDefault();
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault();
    }

}
