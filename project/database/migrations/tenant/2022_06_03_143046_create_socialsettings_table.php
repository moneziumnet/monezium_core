<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialsettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socialsettings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('facebook', 191)->nullable();
            $table->string('gplus', 191)->nullable();
            $table->string('twitter', 191)->nullable();
            $table->string('linkedin', 191)->nullable();
            $table->string('dribble', 191)->nullable();
            $table->tinyInteger('f_status')->default(1);
            $table->tinyInteger('g_status')->default(1);
            $table->tinyInteger('t_status')->default(1);
            $table->tinyInteger('l_status')->default(1);
            $table->tinyInteger('d_status')->default(1);
            $table->tinyInteger('f_check')->nullable();
            $table->tinyInteger('g_check')->nullable();
            $table->text('fclient_id')->nullable();
            $table->text('fclient_secret')->nullable();
            $table->text('fredirect')->nullable();
            $table->text('gclient_id')->nullable();
            $table->text('gclient_secret')->nullable();
            $table->text('gredirect')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('socialsettings');
    }
}
