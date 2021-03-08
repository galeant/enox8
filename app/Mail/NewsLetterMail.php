<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class NewsLetterMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $details,$sender;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details,$sender = NULL)
    {
        //
        $this->details = $details;
        $this->sender = $sender;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->sender);
        if($this->sender !== NULL){
            // switch($this->details['source']){
            //     case 'user':
            //         $subject = $this->details['sender_name'].' has send you email';
            //         break;
            //     case 'admin':
            //         $subject = 'Admin '.$this->details['sender_name'].' has send you email';
            //         break;
            // }
            return $this->from($this->sender)
                ->subject($this->details['subject'])
                ->view('emails.newsletter', [
                'title' => $this->details['title'],
                'content' => $this->details['content']
            ]);
        }else{
            \Carbon\Carbon::setLocale('id');

            $data = $this->details;

            $title = $data['content']->title;
            $content = $data['content']->content;
            return $this->view('emails.newsletter', compact(
                'title',
                'content'
            ));    
        }
    }
}
