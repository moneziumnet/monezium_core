<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

   protected $fillable = ['bank_plan_id','account_number','name', 'photo', 'zip', 'residency', 'city', 'address', 'phone', 'vat', 'email','password','user_type','verification_link','affilate_code','referral_id','is_provider','twofa','go','details','kyc_status','kyc_info','kyc_reject_reason','plan_end_date', 'tenant_id', 'section', 'wallet_maintenance', 'card_maintenance', 'otp_payments', 'country', 'dob', 'kyc_method','company_name','company_type','company_reg_no','company_vat_no','company_address','company_dob','personal_code','your_id','issued_authority','date_of_issue','date_of_expire'];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $dates = [
        'plan_end_date',
    ];

    public function sectionCheck($value){
        $sections = explode(" , ", $this->section);
        if (in_array($value, $sections)){
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
        return $this->hasMany('App\Models\Country','country');
    }
}
