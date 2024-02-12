<?php

namespace App\Jobs;

use App\Events\WeatherUpdated;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UpdateUserWeatherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(protected User $user)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = app(WeatherService::class);
        $weather = $service->getWeather($this->user->latitude, $this->user->longitude);

        if ($weather) {
            Cache::put($service->getCacheKey($this->user->latitude, $this->user->longitude), $weather, now()->addHour());

            // Send the updated weather data to the front-end via a socket
            event(new WeatherUpdated($this->user, $weather));
        }
    }
}
