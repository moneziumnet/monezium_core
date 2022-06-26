<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use phpDocumentor\Reflection\Types\Null_;

class Admin extends Authenticatable
{
    protected $guard = 'admin';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'photo', 'created_at', 'updated_at', 'remember_token','tenant_id', 'zip', 'city', 'address', 'country_id', 'vat', 'plan_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


    public function IsSuper(){
        if ($this->id == 1 && $this->tenant_id == null) {
           return true;
        }
        return false;
    }

    public function sectionCheck($value){
        $sections = explode(" , ", $this->section);
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
    }

}
