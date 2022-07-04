<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_transfers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->integer('other_bank_id')->nullable();
            $table->integer('beneficiary_id')->nullable();
            $table->string('transaction_no')->nullable();
            $table->integer('currency_id')->nullable();
            $table->double('cost')->nullable();
            $table->integer('amount')->nullable();
            $table->double('final_amount')->nullable();
            $table->enum('type', ['own', 'other'])->nullable();
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('balance_transfers');
    }
}
