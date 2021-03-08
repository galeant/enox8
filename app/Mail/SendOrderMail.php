<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class SendOrderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $details;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        //
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Carbon\Carbon::setLocale('id');

        $data = $this->details;

        $title = "Konfirmasi Pengiriman : " . $data['transaction_detail']['product_name'] . "";
        $user = $data['all']['user']['first_name'] . " " . $data['all']['user']['last_name'];
        $tanggal = \Carbon\Carbon::now()->format('l, d F Y, H:i') . " WIB";
        $gross_price = "Rp " . number_format($data['transaction_detail']['product_gross_price'], 0, ',', '.');
        $total_payment = $data['transaction_detail']['product_net_price'];
        $payment_time_limit = \Carbon\CarbonImmutable::now()->add(3, 'day')->format('l, d F Y, H:i') . " WIB";
        $unique_code = $data['all']['unique_code'];
        if ($unique_code !== '') {
            $metode_pembayaran = "" . $data['all']['bank_account_type'] . " <br>
            123 456 7890 <br>
            a/n PT. Noxus <br>
            Cabang Bintaro";
        }

        $payment_reference = "PYM/" . date('Ymd') . "/" . integerToRoman(date('m')) . "/" . integerToRoman(date('y')) . "/" . $data['all']['id'] . "";
        $status = $data['status']['status_name'];
        $invoice_number = "INV/" . date('Ymd') . "/" . integerToRoman(date('m')) . "/" . integerToRoman(date('y')) . "/" . $data['all']['id'] . "";

        $jasa_pengiriman = $data['transaction_detail']['courier'];
        $tanggal_pengiriman = $data['transaction_detail']['updated_at'];
        $quantity = $data['transaction_detail']['qty'];
        $product_net_price = $data['transaction_detail']['product_net_price'];
        $ongkir = 7000;
        $sub_total_payment =  $total_payment + $ongkir;


        return $this->view('emails.send_order', compact(
            'title',
            'user',
            'tanggal',
            'gross_price',
            'payment_time_limit',
            'unique_code',
            'metode_pembayaran',
            'payment_reference',
            'status',
            'invoice_number',
            'jasa_pengiriman',
            'tanggal_pengiriman',
            'quantity',
            'product_net_price',
            'ongkir',
            'sub_total_payment'
        ));
    }
}
