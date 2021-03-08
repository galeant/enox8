<?php

namespace App\Notifications\Dashboard;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegisterNotification extends Notification  implements ShouldQueue
{
    use Queueable;
    public $password, $action;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password, $action)
    {
        $this->password = $password;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $str = 'Email anda baru saja di tambahakan menjadi admin, berikut adalah password untuk login';
        if ($this->action !== NULL) {
            $str = 'Email anda melakukan reset password, berikut password barunya';
        }
        return (new MailMessage)
            ->greeting('Hello!')
            ->line($str)
            ->line($this->password);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
