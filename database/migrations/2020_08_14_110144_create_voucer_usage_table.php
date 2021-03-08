<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucerUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_usage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('trasnaction_id');
            $table->string('voucher_code');
            $table->decimal('voucher_discount_value', 18, 2);
            $table->decimal('total_price', 18, 2);
            $table->decimal('total_payment', 18, 2);

            $table->decimal('voucher_value', 18, 2);
            $table->string('voucher_unit');
            $table->decimal('voucher_max_discount', 18, 2);
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
        Schema::dropIfExists('voucer_usage');
    }
}
