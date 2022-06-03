<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWireTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wire_transfers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('wire_transfer_bank_id')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('currency')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('country')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->double('amount')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 == \'pending\'

1 == \'completed\'

2 == \'reject\'');
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
        Schema::dropIfExists('wire_transfers');
    }
}
