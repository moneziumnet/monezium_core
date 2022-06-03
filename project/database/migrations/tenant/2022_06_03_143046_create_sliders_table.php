<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->text('subtitle_text')->nullable();
            $table->string('subtitle_size', 50)->nullable();
            $table->string('subtitle_color', 50)->nullable();
            $table->string('subtitle_anime', 50)->nullable();
            $table->text('title_text')->nullable();
            $table->string('title_size', 50)->nullable();
            $table->string('title_color', 50)->nullable();
            $table->string('title_anime', 50)->nullable();
            $table->text('details_text')->nullable();
            $table->string('details_size', 50)->nullable();
            $table->string('details_color', 50)->nullable();
            $table->string('details_anime', 50)->nullable();
            $table->string('photo', 191)->nullable();
            $table->string('position', 50)->nullable();
            $table->text('link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sliders');
    }
}
