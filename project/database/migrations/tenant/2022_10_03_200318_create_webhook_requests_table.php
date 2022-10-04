<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_requests', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('sender_address')->nullable();
            $table->string('sender_name')->nullable();
            $table->double('amount')->nullable();
            $table->integer('currency_id')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->string('failure_reason')->nullable();
            $table->string('gateway_type');
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
        Schema::dropIfExists('webhook_requests');
    }
};
