<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Font;

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

        Admin::create([
            'name' => $centralUser->name,
            'email' =>  $centralUser->email,
            'password' =>  $centralUser->password,
            'phone' => $centralUser->phone,
            'role_id' => $centralUser->role_id,
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
