<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title')->nullable();
            $table->decimal('min_amount', 10, 0)->nullable();
            $table->decimal('max_amount', 10, 0)->nullable();
            $table->decimal('per_installment', 10, 0)->nullable();
            $table->integer('installment_interval')->nullable();
            $table->integer('total_installment')->nullable();
            $table->text('instruction')->nullable();
            $table->text('required_information')->nullable();
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
        Schema::dropIfExists('loan_plans');
    }
}
