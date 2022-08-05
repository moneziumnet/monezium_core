<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->mediumText('information');
            $table->string('keyword', 255);
            $table->string('currency_id', 191)->default('["1"]');
            $table->integer('subbank_id');
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
        Schema::dropIfExists('bank_gateways');
    }
}
