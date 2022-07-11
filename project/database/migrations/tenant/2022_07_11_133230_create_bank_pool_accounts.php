<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankPoolAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_pool_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bank_id');
            $table->integer('currency_id');
            $table->decimal('balance', 20, 10)->default(0);
            $table->timestamps();
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
        Schema::dropIfExists('bank_pool_accounts');
    }
}
