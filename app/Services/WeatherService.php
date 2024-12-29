<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
        $this->apiUrl = env('WEATHER_API_URL');
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
        $precipitationThreshold = config('settings.precipitation_threshold');
        $uvIndexThreshold = config('settings.uv_index_threshold');

        $precipitation = $weatherData['rain']['1h'] ?? 0;
        $uvIndex = $weatherData['uvi'] ?? 0;

        return [
            'precipitation' => $precipitation >= $precipitationThreshold,
            'uv_index' => $uvIndex >= $uvIndexThreshold,
        ];
    }
}
