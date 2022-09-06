<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('shop_id');
            $table->integer('cat_id')->nullable;
            $table->string('name');
            $table->string('description')->nullable;
            $table->integer('currency_id');
            $table->double('amount');
            $table->integer('quantity');
            $table->integer('sold')->nullable;
            $table->string('ref_id');
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('products');
    }
}
