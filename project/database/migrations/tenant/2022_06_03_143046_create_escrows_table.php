<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trnx');
            $table->integer('user_id');
            $table->integer('recipient_id');
            $table->text('description');
            $table->decimal('amount', 20, 10);
            $table->boolean('pay_charge')->comment('1 = sender pay charge, 0 = receiver pa charge');
            $table->decimal('charge', 20, 10);
            $table->integer('currency_id');
            $table->boolean('status')->default(false)->comment('0 = on-hold, 1 = release, 3 = disputed
');
            $table->integer('dispute_created')->nullable();
            $table->string('returned_to')->nullable();
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
        Schema::dropIfExists('escrows');
    }
}
