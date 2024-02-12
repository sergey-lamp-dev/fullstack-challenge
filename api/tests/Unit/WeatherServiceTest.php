<?php

namespace Tests\Services;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    private $weatherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->weatherService = new WeatherService();

        // Mock the Http facade for testing purposes
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => [
                    'lon' => -122.08,
                    'lat' => 37.39,
                ],
                'weather' => [
                    [
                        'id' => 800,
                        'main' => 'Clear',
                        'description' => 'clear sky',
                        'icon' => '01d',
                    ],
                ],
                'base' => 'stations',
                'main' => [
                    'temp' => 282.55,
                    'feels_like' => 281.86,
                    'temp_min' => 280.37,
                    'temp_max' => 284.26,
                    'pressure' => 1023,
                    'humidity' => 100,
                ],
                'visibility' => 16093,
                'wind' => [
                    'speed' => 1.5,
                    'deg' => 350,
                ],
                'clouds' => [
                    'all' => 1,
                ],
                'dt' => 1560350645,
                'sys' => [
                    'type' => 1,
                    'id' => 5122,
                    'message' => 0.0139,
                    'country' => 'US',
                    'sunrise' => 1560343627,
                    'sunset' => 1560396563,
                ],
                'timezone' => -25200,
                'id' => 420006353,
                'name' => 'Mountain View',
                'cod' => 200,
            ]),
        ]);
    }

    /** @test */
    public function it_returns_weather_data_for_a_location()
    {
        $latitude = 37.39;
        $longitude = -122.08;

        $weather = $this->weatherService->getWeather($latitude, $longitude);

        // Check that the weather data is an array and has the expected keys
        $this->assertIsArray($weather);
        $this->assertArrayHasKey('coord', $weather);
        $this->assertArrayHasKey('weather', $weather);
        $this->assertArrayHasKey('main', $weather);
        $this->assertArrayHasKey('visibility', $weather);
        $this->assertArrayHasKey('wind', $weather);
        $this->assertArrayHasKey('clouds', $weather);
        $this->assertArrayHasKey('dt', $weather);
        $this->assertArrayHasKey('sys', $weather);
        $this->assertArrayHasKey('timezone', $weather);
        $this->assertArrayHasKey('id', $weather);
        $this->assertArrayHasKey('name', $weather);
        $this->assertArrayHasKey('cod', $weather);

        // Check that the cache was used for subsequent calls to getWeather() with the same parameters
        $this->assertTrue(Cache::has($this->weatherService->getCacheKey($latitude, $longitude)));
        $this->assertEquals($weather, Cache::get($this->weatherService->getCacheKey($latitude, $longitude)));
    }

    /** @test */
    public function testGetWeatherReturnsArray()
    {
        $weather = $this->weatherService->getWeather(43.65, -79.38);
        $this->assertIsArray($weather);
    }

    /** @test */
    public function testGetWeatherReturnsExpectedData()
    {
        $weather = $this->weatherService->getWeather(43.65, -79.38);
        $this->assertArrayHasKey('coord', $weather);
        $this->assertArrayHasKey('weather', $weather);
        $this->assertArrayHasKey('main', $weather);
        $this->assertArrayHasKey('wind', $weather);
        $this->assertArrayHasKey('sys', $weather);
        $this->assertArrayHasKey('name', $weather);
    }

    /** @test */
    public function testGetWeatherReturnsCachedData()
    {
        Cache::shouldReceive('has')->once()->andReturn(true);
        Cache::shouldReceive('get')->once()->andReturn(['coord' => ['lat' => 43.65, 'lon' => -79.38]]);
        Http::shouldReceive('timeout')->never();
        $weather = $this->weatherService->getWeather(43.65, -79.38);
        $this->assertEquals(['coord' => ['lat' => 43.65, 'lon' => -79.38]], $weather);
    }

    /** @test */
    public function testGetCacheKeyReturnsExpectedString()
    {
        $cacheKey = $this->weatherService->getCacheKey(43.65, -79.38);
        $this->assertEquals('weather-43.65--79.38', $cacheKey);
    }
}
