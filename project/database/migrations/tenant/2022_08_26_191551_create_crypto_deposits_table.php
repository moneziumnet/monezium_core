<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('currency_id');
            $table->integer('user_id');
            $table->double('amount');
            $table->string('address')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 == pending , 1 == approve , 2 == rejected');
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
        Schema::dropIfExists('crypto_deposits');
    }
}
