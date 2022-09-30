<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('beneficiary_id');
            $table->string('number');
            $table->integer('currency_id');
            $table->string('invoice_to');
            $table->string('email');
            $table->string('address');
            $table->string('type');
            $table->decimal('charge', 20, 10);
            $table->decimal('final_amount', 20, 10);
            $table->decimal('get_amount', 20, 10);
            $table->integer('product_id')->nullable();
            $table->integer('contract_id')->nullable();
            $table->integer('contract_aoa_id')->nullable();
            $table->mediumText('description');
            $table->mediumText('documents')->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('1 => paid, 0 => not paid');
            $table->tinyInteger('status')->default(0)->comment('1 => published, 0 => not published , 2 => cancel');
            $table->tinyInteger('template')->comment('0 => basic, 1=> classic, 2=> pro');
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
        Schema::dropIfExists('invoices');
    }
}
