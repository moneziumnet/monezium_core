<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractAoasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_aoas', function (Blueprint $table) {
            $table->id();
            $table->integer('contract_id');
            $table->integer('contractor_id');
            $table->string('contractor_type', 255)->default('App\Models\User');
            $table->integer('client_id');
            $table->string('title', 255);
            $table->mediumText('information')->nullable();
            $table->mediumText('default_pattern')->nullable();
            $table->mediumText('pattern')->nullable();
            $table->string('contracter_image_path', 255)->nullable();
            $table->string('customer_image_path', 255)->nullable();
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
        Schema::dropIfExists('contract_aoas');
    }
}
