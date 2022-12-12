<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycRequest extends Model
{
    use HasFactory;
    protected $fillable = ['title','kyc_info', 'request_date','submitted_date','user_id','status'];
    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
