<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_checkouts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ref_id');
            $table->double('amount')->nullbale();
            $table->integer('currency_id');
            $table->string('name');
            $table->text('description')->nullbale();
            $table->integer('shop_id');
            $table->string('redirect_link')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 == disable , 1 == approve');
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
        Schema::dropIfExists('merchant_checkouts');
    }
}
