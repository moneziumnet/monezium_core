<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            'tenant_id' => $centralUser->id,
            'photo' => 'avatar/avatar.png',
        ]);

        $path = public_path('/project/database/database.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
