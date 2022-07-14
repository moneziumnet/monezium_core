<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_loans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('plan_id');
            $table->integer('currency_id')->nullable();
            $table->decimal('loan_amount', 20, 10)->nullable();
            $table->decimal('per_installment_amount', 20, 10)->nullable();
            $table->integer('total_installment')->nullable();
            $table->integer('given_installment')->nullable();
            $table->decimal('paid_amount', 20, 10)->nullable();
            $table->decimal('total_amount', 20, 10)->nullable();
            $table->mediumText('required_information')->nullable();
            $table->timestamp('next_installment')->nullable();
            $table->tinyInteger('status')->nullable()->default(0)->comment('0 == pending , 1 == approve , 2 == rejected, 3 == completed,');
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
        Schema::dropIfExists('user_loans');
    }
}
