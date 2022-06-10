<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $guard = 'admin';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role_id', 'photo', 'created_at', 'updated_at', 'remember_token','tenant_id', 'zip', 'city', 'address', 'country_id', 'vat'
        // , 'full_name', 'birth_date', 'persional_code', 'passport_number', 'issue_date', 'expire_date', 'authority'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


    public function IsSuper(){
        if ($this->id == 1) {
           return true;
        }
        return false;
    }
    
    public function staff_role(){
        return $this->belongsTo('App\Models\Role','role_id')->withDefault();
    }

    public function role()
    {
    	return $this->belongsTo('App\Models\Role')->withDefault(function ($data) {
            foreach($data->getFillable() as $dt){
                $data[$dt] = __('Deleted');
            }
        });
    }

    public function sectionCheck($value){
        $sections = explode(" , ", $this->role->section);
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
    }

}
