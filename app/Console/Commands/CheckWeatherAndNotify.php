<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherService;
use App\Models\User;
use App\Notifications\WeatherAlertNotification;
use Illuminate\Support\Facades\Log;

class CheckWeatherAndNotify extends Command
{
    protected $signature = 'weather:check-and-notify {city}';
    protected $description = 'Check weather for anomalies and notify users if needed';

    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    public function handle()
    {
        $city = $this->argument('city');
        $weatherData = $this->weatherService->getWeatherData($city);

        if (!$weatherData) {
            $this->error('Failed to fetch weather data for the city.');
            return;
        }

        $anomalies = $this->weatherService->checkWeatherAnomalies($weatherData);

        if (!$anomalies['precipitation'] && !$anomalies['uv_index']) {
            $this->info('No weather anomalies detected.');
            return;
        }

        $users = User::all();
        foreach ($users as $user) {
            $preferences = $user->weatherPreferences;

            if (($anomalies['precipitation'] && $preferences->receive_precipitation_alerts) ||
                ($anomalies['uv_index'] && $preferences->receive_uv_alerts)) {
                $user->notify(new WeatherAlertNotification($anomalies));
                $this->info('Notification sent to: ' . $user->email);
            } else {
                $this->info('No notification sent to: ' . $user->email . ' due to preferences.');
            }
        }
    }
}
