<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('logo', 255);
            $table->integer('merchant_id');
            $table->string('document');
            $table->string('url');
            $table->unsignedInteger('status')->default(0)->comment('1 => active, 0 => inactive');
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
        Schema::dropIfExists('merchant_shops');
    }
}
