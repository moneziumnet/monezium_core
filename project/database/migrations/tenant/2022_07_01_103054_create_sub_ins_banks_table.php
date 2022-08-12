<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubInsBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_ins_banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ins_id');
            $table->string('name', 255);
            $table->string('address', 255);
            $table->boolean('status')->default(true)->comment('0 == \'pending\'
1 == \'completed\'
2 == \'reject\'');
            $table->decimal('min_limit', 18, 8);
            $table->decimal('max_limit', 18, 8);
            $table->decimal('fixed_charge', 18, 8)->default(0);
            $table->decimal('percent_charge', 18)->default(0);
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
        Schema::dropIfExists('sub_ins_banks');
    }
}
