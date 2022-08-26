<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_withdraws', function (Blueprint $table) {
            $table->id();
            $table->integer('currency_id');
            $table->integer('user_id');
            $table->double('amount');
            $table->string('hash');
            $table->string('sender_address');
            $table->tinyInteger('status')->default(0)->comment('0 == pending , 1 == approve , 2 == rejected');
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
        Schema::dropIfExists('crypto_withdraws');
    }
}
