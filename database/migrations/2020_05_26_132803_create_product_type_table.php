<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('product_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id');
            $table->string('name')->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('stock');

            $table->decimal('discount_value', 15, 2)->nullable();
            $table->enum('discount_unit', ['decimal', 'percentage'])->nullable();
            $table->date('discount_effective_start_date')->nullable();
            $table->date('discount_effective_end_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('draft')->comment('draft->publish');
            $table->string('image');
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
        Schema::dropIfExists('product_type');
    }
}
