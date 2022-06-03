<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDpsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dps_plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title')->nullable();
            $table->decimal('per_installment', 10, 0)->nullable();
            $table->integer('installment_interval')->nullable();
            $table->integer('total_installment');
            $table->decimal('interest_rate', 10, 0);
            $table->decimal('final_amount', 10, 0);
            $table->decimal('user_profit', 10, 0)->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('dps_plans');
    }
}
