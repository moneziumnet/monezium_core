<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trnx');
            $table->integer('user_id');
            $table->tinyInteger('user_type');
            $table->integer('currency_id');
            $table->integer('wallet_id')->nullable();
            $table->decimal('charge', 20, 10)->default(0);
            $table->decimal('amount', 20, 10);
            $table->string('remark');
            $table->string('type', 10)->nullable();
            $table->string('details');
            $table->string('invoice_num')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
