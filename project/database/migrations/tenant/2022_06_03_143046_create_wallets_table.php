<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->boolean('user_type')->comment('1 => user, 2 => \'merchant\', 3  => \'agent\'');
            $table->integer('currency_id');
            $table->decimal('balance', 20, 10)->default(0);
            $table->tinyInteger('wallet_type')->default(1)->comment('1=>currency, 2=>card, 3=>deposit, 4=>loan, 5=>escrow');
            $table->string('wallet_no');
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
        Schema::dropIfExists('wallets');
    }
}
