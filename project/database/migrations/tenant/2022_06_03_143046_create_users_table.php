<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_plan_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('account_number')->nullable();
            $table->text('name');
            $table->date('dob');
            $table->string('photo', 191)->nullable();
            $table->string('signature', 191)->nullable();
            $table->string('stamp', 191)->nullable();
            $table->text('zip')->nullable();
            $table->text('city')->nullable();
            $table->text('address')->nullable();
            $table->text('street')->nullable();
            $table->string('country')->nullable();
            $table->text('phone')->nullable();
            $table->text('vat')->nullable();
            $table->text('email');
            $table->string('password', 191)->nullable();
            $table->string('holder_id')->nullable();
            $table->string('holder_id_uk')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->tinyInteger('is_provider')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->text('verification_link')->nullable();
            $table->enum('email_verified', ['Yes', 'No'])->default('No');
            $table->text('affilate_code')->nullable();
            $table->boolean('referral_id')->default(false);
            $table->tinyInteger('twofa')->default(0);
            $table->boolean('two_fa_status')->default(false);
            $table->integer('two_fa_code')->nullable();
            $table->string('login_fa_yn')->nullable()->default('N');
            $table->string('login_fa')->nullable();
            $table->string('payment_fa_yn')->nullable()->default('N');
            $table->string('payment_fa')->nullable();
            $table->string('go')->nullable();
            $table->tinyInteger('verified')->default(0);
            $table->integer('manual_kyc')->default(1);
            $table->text('details')->nullable();
            $table->tinyInteger('kyc_status')->default(0)->comment('0 == \'pending\'
1 == \'approve\'
2 == \'rejected\'');
            $table->string('kyc_photo', 191)->nullable();
            $table->string('kyc_token', 191)->nullable();
            $table->mediumText('kyc_info')->nullable();
            $table->enum('kyc_method', ['manual', 'auto'])->default('auto');
            $table->text('kyc_reject_reason')->nullable();
            $table->string('access_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->tinyInteger('is_banned')->default(0)->comment('1 === banned
0 === active');
            $table->timestamp('plan_end_date')->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('user_type')->nullable();
            $table->text('section')->nullable();
            $table->text('modules')->nullable();
            $table->text('otp_payments')->nullable();
            $table->timestamp('wallet_maintenance')->nullable();
            $table->timestamp('card_maintenance')->nullable();

            $table->text('company_name')->nullable();
            $table->text('company_reg_no')->nullable();
            $table->text('company_type')->nullable();
            $table->text('company_vat_no')->nullable();
            $table->text('company_address')->nullable();
            $table->text('company_city')->nullable();
            $table->string('company_zipcode', 255)->nullable();
            $table->string('company_country', 255)->nullable();
            $table->dateTime('company_dob')->nullable();
            $table->text('personal_code')->nullable();
            $table->text('your_id')->nullable();
            $table->string('issued_authority', 255)->nullable();
            $table->dateTime('date_of_issue')->nullable();
            $table->dateTime('date_of_expire')->nullable();
            $table->tinyInteger('website_theme')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
