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
        Schema::create('user_telegrams', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->text('pincode')->nullable();
            $table->bigInteger('chat_id')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 => logout, 1 => login');
            $table->integer('staff_id')->nullable();
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
        Schema::dropIfExists('user_telegrams');
    }
};
