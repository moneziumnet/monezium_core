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
            $table->text('bank_name')->nullable();
            $table->text('address')->nullable();
            $table->text('bank_address')->nullable();
            $table->text('swift_bic')->nullable();
            $table->text('account_iban')->nullable();
            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('registration_no')->nullable();
            $table->text('vat_no')->nullable();
            $table->text('contact_person')->nullable();
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
