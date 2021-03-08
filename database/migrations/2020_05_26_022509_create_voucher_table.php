<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('voucher', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description');
            $table->string('code')->unique();
            $table->integer('limit_per_user')->nullable();
            $table->integer('limit_per_user_per_day')->nullable();
            $table->date('effective_start_date');
            $table->date('effective_end_date');
            $table->decimal('minimum_payment')->nullable();
            $table->decimal('value');
            $table->enum('unit', ['decimal', 'percentage']);
            $table->string('status')->default('draft')->comment('draft->publish');
            $table->decimal('max_discount', 18, 2)->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('voucher');
    }
}
