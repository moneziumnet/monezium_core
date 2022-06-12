<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('full_name');
            $table->date('dob');
            $table->string('personal_code')->nullable();
            $table->string('c_phone')->nullable();
            $table->string('c_email')->nullable();
            $table->text('c_address')->nullable();
            $table->string('c_city')->nullable();
            $table->string('c_zip_code')->nullable();
            $table->string('id_number')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->date('date_of_expire')->nullable();
            $table->date('issued_authority')->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
