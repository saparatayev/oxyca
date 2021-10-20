<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('fio');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('image')->nullable();

            $table->timestamps();

            // foreign key
            $table->unsignedBigInteger('admin_created_id')->nullable();
            $table->foreign('admin_created_id')->references('id')->on('users');
            
            // foreign key
            $table->unsignedBigInteger('admin_updated_id')->nullable();
            $table->foreign('admin_updated_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
