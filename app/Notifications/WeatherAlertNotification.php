<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SMSMessage;

class WeatherAlertNotification extends Notification
{
    use Queueable;

    public $anomalies;

    public function __construct($anomalies)
    {
        $this->anomalies = $anomalies;
    }

    public function via($notifiable)
    {
        return ['mail', 'nexmo']; // Add Nexmo (SMS) as a channel
    }

    public function toMail($notifiable)
    {
        $message = 'Weather Alert: ';

        if ($this->anomalies['precipitation']) {
            $message .= 'High precipitation detected. ';
        }

        if ($this->anomalies['uv_index']) {
            $message .= 'High UV index detected.';
        }

        return (new MailMessage)
            ->line($message)
            ->action('Check Weather', url('/'))
            ->line('Stay safe!');
    }

    public function toNexmo($notifiable)
    {
        $message = 'Weather Alert: ';

        if ($this->anomalies['precipitation']) {
            $message .= 'High precipitation detected. ';
        }

        if ($this->anomalies['uv_index']) {
            $message .= 'High UV index detected.';
        }

        return (new SMSMessage)
            ->content($message);
    }
}
