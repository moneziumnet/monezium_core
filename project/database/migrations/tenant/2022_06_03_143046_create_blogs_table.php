<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->string('title', 191);
            $table->string('slug')->nullable();
            $table->text('details');
            $table->text('photo')->nullable();
            $table->string('source', 191);
            $table->integer('views')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->text('meta_tag')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('tags')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
