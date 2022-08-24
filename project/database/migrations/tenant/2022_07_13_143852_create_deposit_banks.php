<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositBanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_banks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('deposit_number')->nullable();
            $table->integer('user_id')->nullable();
            $table->double('amount')->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('txnid')->nullable();
            $table->string('method')->nullable();
            $table->string('sub_bank_id')->nullable();
            $table->text('details')->nullable();
            $table->string('document')->nullable();
            $table->enum('status', ['pending', 'complete'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_banks');
    }
}
