<?php

namespace App\Models;

use App\Services\WeatherService;
use App\Jobs\UpdateUserWeatherJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'weather'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_weather_update' => 'datetime'
    ];

    /**
     * Get the weather data for the user.
     *
     * @return array|null
     */
    public function getWeatherAttribute()
    {
        $weather = Cache::remember(
            app(WeatherService::class)->getCacheKey($this->latitude, $this->longitude),
            now()->addHour(),
            function () {
                return app(WeatherService::class)->getWeather($this->latitude, $this->longitude);
            }
        );

        // TODO: this code is for updatig the weather data in the background

        // $cacheKey = app(WeatherService::class)->getWeatherCacheKey();
        // $weather = Cache::get($cacheKey);

        // if (!$weather) {
        //     $this->queueWeatherUpdate();
        //     return null;
        // }

        return $weather['weather'][0]['description'] ?? null;
    }

    /**
     * Queue a job to update the user's weather data.
     */
    public function queueWeatherUpdate()
    {
        UpdateUserWeatherJob::dispatch($this);
    }
}
