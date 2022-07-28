<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanDetail extends Model
{

    public function plan()
    {
        return $this->belongsTo(BankPlan::class,'plan_id')->withDefault();
    }
}
