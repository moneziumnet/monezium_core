<?php

namespace Database\Seeders;

use App\Models\Font;
use App\Models\Page;
use App\Models\Role;
use App\Models\Admin;
use App\Models\Charge;
use App\Models\Country;
use App\Models\DpsPlan;
use App\Models\FdrPlan;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Language;
use App\Models\LoanPlan;
use App\Models\Pagesetting;
use App\Models\CustomerType;
use App\Models\AdminLanguage;
use App\Models\Socialsetting;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    public function run()
    {
        $centralUser = tenancy()->central(function ($tenant) {
            return Admin::find($tenant->id);
        });

        // clone role table
        $role = tenancy()->central(function ($tenant) use ($centralUser){
            return Role::find($centralUser->role_id);
        }); 
        $new_role = $role->replicate();
        $new_role->push();

        // clone paymentgateway table
        $payments = tenancy()->central(function ($tenant) {
            return PaymentGateway::all();
        });
        foreach ($payments as $payment) {
            $new_payment = $payment->replicate();
            $new_payment->save();
        }

        // clone page setting table
        $ps = tenancy()->central(function ($tenant) {
            return Pagesetting::first();
        });
        $ps = $ps->replicate();
        $ps->save();

        // clone page setting table
        $ss = tenancy()->central(function ($tenant) {
            return Socialsetting::first();
        });
        $ss = $ss->replicate();
        $ss->save();
        
        // clone pricing plan table
        $plan = tenancy()->central(function ($tenant) {
            return BankPlan::first();
        });
        $new_plan = $plan->replicate();
        $new_plan->save();

        // clone charges table
        $charges = tenancy()->central(function ($tenant) use($plan) {
            return Charge::where('plan_id', $plan->id)->get();
        }); 

        foreach ($charges as $charge) {
            $charge = $charge->replicate();
            $charge->plan_id = $new_plan->id;
            $charge->save();
        }

        // clone admin_language table
        $admin_language = tenancy()->central(function ($tenant) {
            return AdminLanguage::where('is_default',1)->first();
        });
        $new_admin_language = $admin_language->replicate();
        $new_admin_language->save();
        
        // clone user_language table
        $user_language = tenancy()->central(function ($tenant) {
            return Language::where('is_default',1)->first();
        });
        $new_user_language = $user_language->replicate();
        $new_user_language->save();

        // clone pages table
        $pages = tenancy()->central(function ($tenant) {
            return Page::all();
        });
        foreach ($pages as $page) {
            $page = $page->replicate();
            $page->save();
        }
        
        // clone loan_plan table
        $loan_palns = tenancy()->central(function ($tenant) {
            return LoanPlan::all();
        });
        foreach ($loan_palns as $loan_paln) {
            $loan_paln = $loan_paln->replicate();
            $loan_paln->save();
        }
        // clone dps_plan table
        $dps_plans = tenancy()->central(function ($tenant) {
            return DpsPlan::all();
        });
        foreach ($dps_plans as $dps_plan) {
            $dps_plan = $dps_plan->replicate();
            $dps_plan->save();
        }
        // clone fdr_plan table
        $fdr_plans = tenancy()->central(function ($tenant) {
            return FdrPlan::all();
        });
        foreach ($fdr_plans as $fdr_plan) {
            $fdr_plan = $fdr_plan->replicate();
            $fdr_plan->save();
        }

        // clone customer type table
        $c_types = tenancy()->central(function ($tenant) {
            return CustomerType::all();
        });
        foreach ($c_types as $c_type) {
            $c_type = $c_type->replicate();
            $c_type->save();
        }


        Admin::create([
            'name' => $centralUser->name,
            'email' =>  $centralUser->email,
            'password' =>  $centralUser->password,
            'phone' => $centralUser->phone,
            'role_id' => $new_role->id,
            'tenant_id' => $centralUser->id,
            'photo' => 'avatar/avatar.png',
        ]);

        Country::create([
            'name' => 'United States of America',
            'iso2' =>  'US',
            'iso3' =>  'USA',
            'phone_code' => '1',
        ]);
        
        Currency::create([
            'curr_name' => 'United State Dollar',
            'code' =>  'USD',
            'symbol' =>  '$',
            'rate' =>  1,
            'type' =>  1,
            'status' =>  1,
            'is_default' => 1,
        ]);
        Font::create([
            'font_family' => 'Manrope',
            'font_value' =>  'Manrope',
            'is_default' =>  1,
        ]);
    }
}
