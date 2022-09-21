<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_wallets', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id');
            $table->integer('currency_id');
            $table->decimal('balance', 20, 10)->default(0);
            $table->tinyInteger('shop_id');
            $table->string('wallet_no');
            $table->string('keyword')->nullable();
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
        Schema::dropIfExists('merchant_wallets');
    }
}
