<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title')->nullable();
            $table->double('amount')->nullable();
            $table->double('daily_send')->nullable();
            $table->double('monthly_send')->nullable();
            $table->double('daily_receive')->nullable();
            $table->double('monthly_receive')->nullable();
            $table->double('daily_withdraw')->nullable();
            $table->double('monthly_withdraw')->nullable();
            $table->double('loan_amount')->nullable();
            $table->text('attribute')->nullable();
            $table->integer('days')->nullable();
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
        Schema::dropIfExists('bank_plans');
    }
}
