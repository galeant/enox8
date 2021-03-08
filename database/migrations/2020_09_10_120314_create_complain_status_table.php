<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplainStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            /*
            [
                'Waiting action' => 'auto'
                'Decline' => 'triger oleh admin',
                'CashReturnProcess' => 'di trigger oleh admin dengann kodisi type complaint adalah return cash, jadi saat di accept statusnya jadi ini',
                'CashReturnComplete' => 'di trigger oleh user, saat user udah konfirmasi bahwa dana sudah di kembalikan oelh user',
                'productReturnToStore' => 'di trigger oleh user saat si user menenkan tobol return',
                'productReturnToUser' => 'di trigger oleh store saat store sudah mengirim kembali barang ke user',
                'productReturnComplete' => 'di trigger oleh user saat barang return sudah sampai oleh user',
                'closed' => 'di triger oleh user atau admin'
            ]
            */
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
        Schema::dropIfExists('complain_status');
    }
}
