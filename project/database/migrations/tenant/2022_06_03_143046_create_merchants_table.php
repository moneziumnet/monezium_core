<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('business_name');
            $table->string('name');
            $table->string('email');
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('zip', 25)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->boolean('email_verified')->nullable();
            $table->string('verification_link')->nullable();
            $table->integer('verify_code')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('kyc_status')->default(false);
            $table->text('kyc_info')->nullable();
            $table->string('kyc_reject_reason')->nullable();
            $table->string('access_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->boolean('two_fa_status')->default(false);
            $table->boolean('two_fa')->nullable()->default(false);
            $table->integer('two_fa_code')->nullable();
            $table->timestamps();

            $table->index(['id'], 'id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
