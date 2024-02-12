<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WeatherController
 * @package App\Http\Controllers
 */
class WeatherController extends Controller
{
    /**
     * Create a new WeatherController instance.
     *
     * @param  \App\Services\WeatherService  $weatherService
     * @return void
     */
    public function __construct(protected WeatherService $weatherService)
    {
    }

    /**
     * Get the weather data for a user.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    public function getUserWeather(User $user): JsonResponse
    {
        $weatherData = $this->weatherService->getWeather(
            $user->latitude,
            $user->longitude
        );

        return response()->json(
            $weatherData,
            Response::HTTP_OK
        );
    }
}
