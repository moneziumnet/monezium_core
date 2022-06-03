<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFdrPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fdr_plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title')->nullable();
            $table->decimal('min_amount', 10, 0)->nullable();
            $table->decimal('max_amount', 10, 0)->nullable();
            $table->integer('interest_interval')->nullable();
            $table->string('interval_type')->nullable();
            $table->decimal('interest_rate', 10, 0)->nullable();
            $table->integer('matured_days')->nullable();
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
        Schema::dropIfExists('fdr_plans');
    }
}
