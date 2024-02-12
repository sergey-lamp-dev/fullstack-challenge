<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Get the weather data for a location.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return string
     */
    public function getWeather(float $latitude, float $longitude): array
    {
        $cacheKey = $this->getCacheKey($latitude, $longitude);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $weather = $this->fetchWeatherData($latitude, $longitude);

        if ($weather !== null) {
            Cache::put($cacheKey, $weather, now()->addHour());
        } else {
            $weather = Cache::get($cacheKey);
        }

        return $weather;
    }

    /**
     * Fetch weather data from external API.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return array|null
     */
    protected function fetchWeatherData(float $latitude, float $longitude): ?array
    {
        $response = Http::timeout(500)->get(config('services.openweather.url'), [
            'lat' => $latitude,
            'lon' => $longitude,
            'appid' => config('services.openweather.key'),
            'units' => 'metric',
        ]);

        if ($response->ok()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get the cache key for a location.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return string
     */
    public function getCacheKey(float $latitude, float $longitude): string
    {
        return 'weather-' . $latitude . '-' . $longitude;
    }
}
