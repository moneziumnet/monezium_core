<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignDonation extends Model
{
    protected $fillable = [
        'campaign_id',
        'user_name',
        'payment',
        'amount',
        'currency_id',
        'description',
        'status',
    ];

    public function campaign(){
        return $this->belongsTo(Campaign::class, 'campaign_id')->withDefault();
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault();
    }

}
