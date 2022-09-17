<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcoTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ico_tokens', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->integer('user_id');
            $table->integer('currency_id');
            $table->integer('total_supply');
            $table->integer('balance')->default(0);
            $table->integer('status')->default(0);
            $table->double('price');
            $table->date('end_date');
            $table->string('white_paper');

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
        Schema::dropIfExists('ico_tokens');
    }
}
