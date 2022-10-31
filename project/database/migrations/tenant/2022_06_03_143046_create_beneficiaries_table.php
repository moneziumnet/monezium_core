<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('address')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('swift_bic')->nullable();
            $table->string('account_iban')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('vat_no')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('type', ['RETAIL', 'CORPORATE'])->default('RETAIL')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beneficiaries');
    }
}
