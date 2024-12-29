<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Channels\TwilioSmsChannel;

class WeatherAlertNotification extends Notification
{
    protected array $weatherData;

    public function __construct(array $weatherData)
    {
        $this->weatherData = $weatherData;
    }

    public function via(): array
    {
        return ['mail', TwilioSmsChannel::class];
    }

    /**
     * Build the mail message.
     *
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Weather Alert: Anomaly Detected!')
            ->line('We have detected an anomaly in the weather conditions:')
            ->line('Precipitation: ' . $this->weatherData['precipitation'] . ' mm')
            ->line('UV Index: ' . $this->weatherData['uv_index'])
            ->line('Please take necessary precautions.')
            ->action('View More Details', url('/weather/details'))
            ->line('Thank you for using our weather service!');
    }

    /**
     * Build the SMS message.
     *
     * @return string
     */
    public function toTwilioSms(): string
    {
        return 'Weather Alert: High precipitation (' . $this->weatherData['precipitation'] . ' mm) or UV Index (' . $this->weatherData['uv_index'] . ') detected. Take necessary precautions.';
    }
}
