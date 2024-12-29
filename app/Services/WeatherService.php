<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
    }

    public function getWeatherData($city)
    {
        $response = Http::get($this->apiUrl, [
            'q' => $city,
            'appid' => $this->apiKey,
            'units' => 'metric',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to fetch weather data', ['city' => $city]);

        return null;
    }

    public function checkWeatherAnomalies($weatherData)
    {
        $precipitationThreshold = 50; // mm of rain
        $uvIndexThreshold = 7;        // UV index value

        $precipitation = $weatherData['rain']['1h'] ?? 0;
        $uvIndex = $weatherData['uvi'] ?? 0;

        return [
            'precipitation' => $precipitation >= $precipitationThreshold,
            'uv_index' => $uvIndex >= $uvIndexThreshold,
        ];
    }
}
