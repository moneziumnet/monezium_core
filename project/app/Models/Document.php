<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['ins_id','name','file'];
    use HasFactory;
    public $timestamps = false;
}