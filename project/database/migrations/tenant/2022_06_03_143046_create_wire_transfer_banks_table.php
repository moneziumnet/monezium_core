<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWireTransferBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wire_transfer_banks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title')->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('country_id')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('routing_number')->nullable();
            $table->double('min_amount')->nullable();
            $table->double('max_amount')->nullable();
            $table->double('fixed_charge')->nullable();
            $table->double('percentage_charge')->nullable();
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
        Schema::dropIfExists('wire_transfer_banks');
    }
}
