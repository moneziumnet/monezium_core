<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFdrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_fdrs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('currency_id')->nullable();
            $table->integer('fdr_plan_id')->nullable();
            $table->double('amount')->nullable();
            $table->string('profit_type')->nullable();
            $table->double('profit_amount')->nullable();
            $table->double('interest_rate')->nullable();
            $table->timestamp('next_profit_time')->nullable();
            $table->timestamp('matured_time')->nullable();
            $table->tinyInteger('status')->nullable()->comment('1 == running, 2 == closed');
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
        Schema::dropIfExists('user_fdrs');
    }
}
