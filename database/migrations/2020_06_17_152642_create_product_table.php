<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('main_image')->nullable();
            $table->text('description');
            $table->string('meta_title');
            $table->string('meta_description');
            $table->bigInteger('store_id');
            $table->string('code');
            $table->float('rating', 5, 2)->default(0);
            $table->boolean('is_featured')->default(false);
            $table->decimal('display_price', 15, 2)->nullable();

            $table->string('weight')->nullable();
            $table->string('condition')->nullable();
            $table->string('minimum_order')->nullable();
            $table->string('insurance')->nullable();

            $table->string('status')->default('draft')->comment('draft->publish');

            $table->timestamps();
            $table->softDeletes();

            // $table->decimal('ordering_price',15,2);
            // $table->decimal('weight',10,2);
            // $table->string('condition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product');
    }
}
