<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transaction_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id');

            $table->bigInteger('product_id');
            $table->string('product_slug');
            $table->string('product_name');

            $table->bigInteger('type_id');
            $table->string('type_name');

            $table->integer('qty');

            $table->decimal('product_price');
            $table->decimal('product_discount');

            $table->decimal('total_price');
            $table->decimal('total_discount');
            $table->decimal('total_payment');

            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transaction')
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
        Schema::dropIfExists('transaction_detail');
    }
}
