<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInviteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_users', function (Blueprint $table) {
            // $table->id();
            $table->increments('id', 21);
            $table->bigInteger('user_id')->unsigned();
            $table->string('invited_to')->nullable();
            $table->enum('invite_type', ['Email', 'SMS']);
            $table->enum('status', ['Not Send', 'Sent']);
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
        Schema::dropIfExists('invite_users');
    }
}
