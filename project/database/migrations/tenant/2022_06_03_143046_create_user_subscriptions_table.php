<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('subscription_number')->nullable();
            $table->string('txnid')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('bank_plan_id')->nullable();
            $table->integer('currency_id')->nullable();
            $table->double('price')->nullable();
            $table->string('method')->nullable();
            $table->integer('days')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
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
        Schema::dropIfExists('user_subscriptions');
    }
}
