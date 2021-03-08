<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();

            $table->boolean('can_access_customer')->default(false);
            $table->boolean('can_access_admin')->default(false);
            $table->boolean('can_access_super_admin')->default(false);
            $table->string('activation_token', 255)->nullable();
            $table->dateTime('activation_time_limit')->nullable();
            $table->bigInteger('role_id')->nullable();

            $table->bigInteger('store_id')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')
                ->references('id')
                ->on('store')
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
        Schema::dropIfExists('users');
    }
}
