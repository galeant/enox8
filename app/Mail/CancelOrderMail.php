<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class CancelOrderMail extends Mailable
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

        $title = "Pembayaran Transfer Manual Anda pada taanggal ".\Carbon\Carbon::now()->format('d F Y')." telah kami batalkan";
        $tanggal = \Carbon\Carbon::now()->format('l, d F Y, H:i')." WIB";
        $total_payment = "Rp ".number_format($data['all']['net_price'] , 0, ',', '.');
        $payment_time_limit = \Carbon\CarbonImmutable::now()->add(3, 'day')->format('l, d F Y, H:i')." WIB";
        $unique_code = $data['all']['unique_code'];
        if($unique_code !== ''){
            $metode_pembayaran = "".$data['all']['bank_account_type']." <br>
            123 456 7890 <br>
            a/n PT. Noxus <br>
            Cabang Bintaro";
        }

        $payment_reference = "PYM/".date('Ymd')."/".integerToRoman(date('m'))."/".integerToRoman(date('y'))."/".$data['all']['id']."";
        $payment_status = $data['all']['transaction_detail']['0']['status']['status_name']; //get only one status
        $invoice_number = "INV/".date('Ymd')."/".integerToRoman(date('m'))."/".integerToRoman(date('y'))."/".$data['all']['id']."";
        $transaction_detail = $data['all']['transaction_detail'];

        return $this->view('emails.cancel_order', compact(
            'title',
            'tanggal',
            'total_payment',
            'payment_time_limit',
            'unique_code',
            'metode_pembayaran',
            'payment_reference',
            'payment_status',
            'invoice_number',
            'transaction_detail'
        ));
    }
}
