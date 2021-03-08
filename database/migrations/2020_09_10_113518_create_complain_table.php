<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->biginteger('transaction_id');
            $table->bigInteger('transaction_detail_id');
            $table->integer('qty')->nullable();

            $table->string('complaint');
            $table->bigInteger('status_id');

            $table->string('user_return_evidence')->nullable();
            $table->string('store_evidence')->nullable();
            $table->string('compensate_type')->nullable();

            $table->bigInteger('transaction_return_id');
            $table->decimal('cash_return_value', 18, 2);
            $table->json('complaint_evidence')->nullable();
            $table->timestamps();

            $table->bigInteger('return_transaction_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complain');
    }
}
