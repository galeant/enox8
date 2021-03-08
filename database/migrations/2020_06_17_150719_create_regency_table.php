<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('regencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('province_id');
            $table->timestamps();


            $table->foreign('province_id')
                ->references('id')
                ->on('provinces')
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
        Schema::dropIfExists('regency');
    }
}
