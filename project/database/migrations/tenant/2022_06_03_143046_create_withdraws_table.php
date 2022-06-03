<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraws', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('txnid')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('method')->nullable();
            $table->text('address')->nullable();
            $table->text('reference')->nullable();
            $table->float('amount', 10, 0)->nullable();
            $table->float('fee', 10, 0)->nullable()->default(0);
            $table->text('details')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->enum('type', ['user', 'vendor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdraws');
    }
}
