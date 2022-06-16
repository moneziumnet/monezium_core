<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ins_id'];

    public function institution()
    {
        return $this->belongsTo(Admin::class, 'ins_id','id');
    }
}
