<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dps', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('currency_id')->nullable();
            $table->integer('dps_plan_id')->nullable();
            $table->decimal('per_installment', 10, 0)->nullable();
            $table->integer('installment_interval')->nullable();
            $table->decimal('total_installment', 10, 0)->nullable();
            $table->decimal('given_installment', 10, 0)->nullable();
            $table->decimal('deposit_amount', 10, 0)->nullable();
            $table->decimal('matured_amount', 10, 0)->nullable();
            $table->double('paid_amount')->default(0);
            $table->decimal('interest_rate', 10, 0)->nullable();
            $table->tinyInteger('status')->nullable()->comment('1 == running, 2 == matured');
            $table->tinyInteger('is_given')->default(0);
            $table->timestamp('next_installment')->nullable();
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
        Schema::dropIfExists('user_dps');
    }
}
