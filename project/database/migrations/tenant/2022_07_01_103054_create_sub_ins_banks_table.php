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
            $table->string('name', 100);
            $table->string('address', 100);
            $table->string('swift', 100);
            $table->string('iban', 100);
            $table->decimal('min_limit', 18, 8);
            $table->decimal('max_limit', 18, 8);
            $table->decimal('daily_maximum_limit', 18, 8)->default(0);
            $table->decimal('monthly_maximum_limit', 18, 8)->default(0);
            $table->integer('monthly_total_transaction')->default(0)->comment('Count');
            $table->integer('daily_total_transaction')->default(0)->comment('Count');
            $table->decimal('fixed_charge', 18, 8)->default(0);
            $table->decimal('percent_charge', 18)->default(0);
            $table->string('processing_time', 100);
            $table->text('instruction')->nullable();
            $table->mediumText('required_information')->nullable();
            $table->boolean('status')->default(true)->comment('0 == \'pending\'
1 == \'completed\'
2 == \'reject\'');
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
