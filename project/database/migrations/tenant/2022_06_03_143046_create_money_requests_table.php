<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_requests', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->string('receiver_name')->nullable();
            $table->integer('currency_id')->nullable();
            $table->double('cost')->nullable();
            $table->double('amount')->nullable();
            $table->tinyInteger('status')->default(0)->comment('1 == success
0 == pending');
            $table->tinyInteger('user_type');
            $table->text('details')->nullable();
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
        Schema::dropIfExists('money_requests');
    }
}
