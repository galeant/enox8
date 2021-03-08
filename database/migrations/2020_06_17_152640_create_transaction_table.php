<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transaction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();

            $table->string('transaction_code')->nullable();
            $table->string('invoice_number')->nullable();

            $table->bigInteger('store_id');
            $table->string('store_name');

            $table->decimal('total_price');
            $table->decimal('total_payment')->nullable();

            $table->decimal('total_product_discount', 18, 2)->nullable();
            $table->decimal('total_voucher_discount', 18, 2)->nullable();
            $table->decimal('total_price_discount', 18, 2)->nullable();

            $table->string('sender_name');
            $table->string('sender_address');
            $table->string('sender_country');
            $table->string('sender_province');
            $table->string('sender_regency');
            $table->string('sender_district');
            $table->string('sender_village');
            $table->string('sender_postal_code');
            $table->string('sender_phone');
            $table->string('sender_email');

            $table->string('recipient_name');
            $table->string('recipient_address');
            $table->string('recipient_country');
            $table->string('recipient_province');
            $table->string('recipient_regency');
            $table->string('recipient_district');
            $table->string('recipient_village');
            $table->string('recipient_phone');
            $table->string('recipient_postal_code');
            $table->string('recipient_latitude')->nullable();
            $table->string('recipient_longitude')->nullable();

            $table->string('bank_id');
            $table->string('bank_name');
            $table->string('bank_account_type');
            $table->string('store_bank_account_number')->nullable();

            $table->string('buyer_bank_account_name')->nullable();
            $table->string('buyer_bank_account_number')->nullable();

            $table->smallInteger('unique_code')->nullable();
            $table->string('payment_evidence')->nullable();
            $table->string('payment_return_evidence')->nullable();
            $table->bigInteger('status_id');

            $table->bigInteger('courier_id');
            $table->string('courier_name');
            $table->string('courier_code');
            $table->string('courier_service_name');
            $table->decimal('courier_price', 18, 2);
            $table->integer('delivery_duration');
            $table->string('resi_number')->nullable();

            $table->decimal('insurance_fee', 18, 2)->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('transaction_status')
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
        Schema::dropIfExists('transaction');
    }
}
