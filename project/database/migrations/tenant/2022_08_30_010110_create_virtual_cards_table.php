<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->integer('account_id');
            $table->string('card_hash');
            $table->string('card_pan');
            $table->string('masked_card');
            $table->integer('cvv');
            $table->string('expiration');
            $table->string('card_type');
            $table->text('name_on_card');
            $table->text('callback');
            $table->string('secret')->nullable();
            $table->double('amount');
            $table->string('rate')->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('charge');
            $table->integer('status')->default(1);
            $table->string('ref_id')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->text('address')->nullable();
            $table->text('zip_code')->nullable();
            $table->string('bg')->nullable();
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
        Schema::dropIfExists('virtual_cards');
    }
}
