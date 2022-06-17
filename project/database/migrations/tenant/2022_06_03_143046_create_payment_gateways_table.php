<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ins_id',11)->default(0);
            $table->string('subtitle', 191)->nullable();
            $table->string('title', 191)->nullable();
            $table->text('details')->nullable();
            $table->string('name', 100)->nullable();
            $table->enum('type', ['manual', 'automatic'])->nullable()->default('manual');
            $table->mediumText('information')->nullable();
            $table->string('keyword', 191)->nullable();
            $table->string('currency_id', 191)->default('0');
            $table->integer('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateways');
    }
}
