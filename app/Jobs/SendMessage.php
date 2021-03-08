<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\NewsLetterMail as NewsLetterMail;
use Mail;

use App\Models\V1\Store;


class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $data,$source;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$source)
    {
        $this->data = $data;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sender_name = $this->data['user']->first_name.' '.$this->data['user']->last_name;
        $sender = $this->data['user']->email;
        $to = $this->data['store']->email;
        if($this->source === 'admin'){
            $sender_name = $this->data['store']->name;
            $sender = $this->data['store']->email;
            $to = $this->data['user']->email;
        }
        $data = [
            'title' => 'New Message',
            'subject' => $this->data['message']->subject,
            'content' => $this->data['message']->content,
            'source' => $this->source,
            'sender_name' => $sender_name
        ];
        
        $email = new NewsLetterMail($data,$sender);
        Mail::to($to)->send($email);
        $this->data['message']->is_send = true;
        $this->data['message']->save();
    }
}
