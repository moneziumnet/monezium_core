<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 191)->nullable();
            $table->tinyInteger('plan_id')->default(1);
            $table->string('photo', 191)->nullable();
            $table->string('zip', 191)->nullable();
            $table->string('city', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('country_id')->nullable();
            $table->string('vat', 191)->nullable();
            $table->string('password', 191);
            $table->tinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->string('tenant_id')->nullable();
            $table->text('section')->nullable();
            $table->enum('role', ['admin', 'staff'])->default('admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
