<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['full_name','dob','personal_code','c_phone','c_email','c_address','c_city','c_zip_code','c_country','id_number','date_of_issue','date_of_expire','issued_authority'];
    use HasFactory;
    
}
