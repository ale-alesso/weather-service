<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WeatherAlertNotification;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
        $this->apiUrl = env('WEATHER_API_URL');
    }

    /**
     * Check weather conditions and notify users if anomalies are detected.
     *
     * @param string $city
     * @return void
     */
    public function checkWeatherAndNotify($city)
    {
        $weatherData = $this->fetchWeatherData($city);

        $precipitationThreshold = config('settings.precipitation_threshold');
        $uvIndexThreshold = config('settings.uv_index_threshold');

        if ($weatherData['precipitation'] > $precipitationThreshold || $weatherData['uv_index'] > $uvIndexThreshold) {
            $users = User::all();

            foreach ($users as $user) {
                $user->notify(new WeatherAlertNotification($weatherData));
            }
        }
    }

    /**
     * Fetch weather data from an external weather API.
     *
     * @param string $city
     * @return array
     */
    private function fetchWeatherData($city)
    {
        // Example API call to fetch weather data (replace with actual API service)
        $response = Http::get($this->apiUrl, [
            'key' => $this->apiKey,
            'q' => $city,
        ]);

        $data = $response->json();

        return [
            'condition' => $data['current']['condition']['text'] ?? 'Unknown',
            'precipitation' => $data['current']['precip_mm'] ?? 0,
            'uv_index' => $data['current']['uv'] ?? 0,
        ];
    }
}
