<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('user_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone');
            $table->string('subscribe')->nullable();
            $table->text('avatar')->nullable();
            $table->text('avatar_original')->nullable();

            $table->string('gender')->nullable();
            $table->date('birthdate')->nullable();
            $table->timestamps();
            // $table->string('fcm_token');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_detail');
    }
}
