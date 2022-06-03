<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKycFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kyc_forms', function (Blueprint $table) {
            $table->integer('id', true);
            $table->tinyInteger('user_type')->nullable();
            $table->integer('type')->nullable();
            $table->string('label');
            $table->string('name');
            $table->tinyInteger('required')->default(0);
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
        Schema::dropIfExists('kyc_forms');
    }
}
