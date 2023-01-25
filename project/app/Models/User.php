<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

   protected $fillable = ['bank_plan_id','account_number','name', 'photo', 'zip', 'residency', 'city', 'address', 'phone', 'vat', 'email','password','user_type','verification_link','affilate_code','referral_id','is_provider','twofa','go','details','kyc_status','kyc_info','kyc_reject_reason','plan_end_date', 'tenant_id', 'section', 'wallet_maintenance', 'card_maintenance', 'otp_payments', 'country', 'dob', 'kyc_method','company_name','company_type','company_city','company_country','company_number','company_reg_no','company_vat_no','company_address','company_dob','company_zipcode','personal_code','your_id','issued_authority','date_of_issue','date_of_expire', 'modules'];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $dates = [
        'plan_end_date',
    ];

    protected $casts = [
        'name' => 'encrypted',
        'zip' => 'encrypted',
        'city' => 'encrypted',
        'address' => 'encrypted',
        'street' => 'encrypted',
        'phone' => 'encrypted',
        'vat' => 'encrypted',
        'email' => 'encrypted',
        'company_name' => 'encrypted',
        'company_type' => 'encrypted',
        'company_reg_no' => 'encrypted',
        'company_vat_no' => 'encrypted',
        'company_address' => 'encrypted',
        'company_city' => 'encrypted',
        'personal_code' => 'encrypted',
        'your_id' => 'encrypted',
    ];

    public function sectionCheck($value){
        $sections = explode(" , ", $this->section);
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
    }

    public function moduleCheck($value){
        $modules = explode(" , ", $this->modules);
        if (in_array($value, $modules)){
            return true;
        }else{
            return false;
        }
    }

    public function paymentCheck($value){
        $otp_payments = explode(" , ", $this->otp_payments);
        if (in_array($value, $otp_payments)){
            return true;
        }else{
            return false;
        }
    }

    public function subscriptions(){
        return $this->hasMany(UserSubscription::class);
    }

    public function balanceTransfers(){
        return $this->hasMany(BalanceTransfer::class);
    }

    public function fdr(){
        return $this->hasMany(UserFdr::class);
    }

    public function dps(){
        return $this->hasMany(UserDps::class);
    }

    public function loans(){
        return $this->hasMany(UserLoan::class);
    }

    public function wiretransfers(){
        return $this->hasMany(WireTransfer::class);
    }

    public function deposits(){
        return $this->hasMany(Deposit::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdrawals::class);
    }

	public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }
    public function socialProviders()
    {
        return $this->hasMany('App\Models\SocialProvider');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction','user_id');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country','country')->withDefault();
    }

    public function company_country()
    {
        return $this->belongsTo('App\Models\Country','company_country')->withDefault();
    }
}
