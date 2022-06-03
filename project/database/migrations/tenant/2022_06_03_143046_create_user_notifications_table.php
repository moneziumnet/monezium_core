<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('order_id')->default(0);
            $table->integer('withdraw_id')->default(0);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->enum('type', ['Invest', 'Payout', 'Withdraw']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notifications');
    }
}
