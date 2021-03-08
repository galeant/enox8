<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherToRelationTabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('voucher_to_relation', function (Blueprint $table) {
            $table->bigInteger('voucher_id');
            $table->bigInteger('relation_id');
            $table->enum('type', [
                'product',
                'category'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_to_relation_tabel');
    }
}
