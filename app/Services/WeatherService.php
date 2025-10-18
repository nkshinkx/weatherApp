<?php

namespace App\Services;

use App\Models\ApiRequest;
use App\Models\Forecast;
use App\Models\Location;
use App\Models\WeatherRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openweathermap.org';

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
    }


    public function getWeatherByLocationName(string $locationName): array
    {
        $location = Location::where('name', $locationName)->first();

        if ($location && $location->hasCoordinates()) {
            $latitude = $location->latitude;
            $longitude = $location->longitude;
            $locationId = $location->id;
        } else {
            $coordinates = $this->getCoordinates($locationName, null);

            if (!$coordinates) {
                throw new \Exception('Could not find coordinates for this location.');
            }

            if ($location) {
                $location->update([
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                ]);
            } else {
                $location = Location::create([
                    'name' => $locationName,
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                    'status' => 'active',
                ]);
            }

            $latitude = $coordinates['lat'];
            $longitude = $coordinates['lon'];
            $locationId = $location->id;
        }

        $weatherData = $this->getCurrentWeather($latitude, $longitude, $locationId);

        if (!$weatherData) {
            throw new \Exception('Could not fetch weather data.');
        }

        $weatherRecord = WeatherRecord::create([
            'location_id' => $locationId,
            'temperature' => $weatherData['temp'],
            'humidity' => $weatherData['humidity'],
            'pressure' => $weatherData['pressure'],
            'wind_speed' => $weatherData['wind_speed'],
            'condition' => $weatherData['condition'],
            'icon' => $weatherData['icon'],
            'fetched_at' => now(),
        ]);

        return [
            'location' => $location,
            'weather' => $weatherRecord,
        ];
    }


    private function getCoordinates(string $locationName, ?int $locationId = null): ?array
    {
        $startTime = microtime(true);
        $endpoint = "{$this->baseUrl}/geo/1.0/direct";

        try {
            $response = Http::withoutVerifying()->get($endpoint, [
                'q' => $locationName,
                'limit' => 1,
                'appid' => $this->apiKey,
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                $response->status(),
                $responseTime,
                $response->successful() ? null : $response->body()
            );

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                return [
                    'lat' => $data['lat'],
                    'lon' => $data['lon'],
                ];
            }

            return null;
        } catch (\Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                500,
                $responseTime,
                $e->getMessage()
            );

            Log::error('Failed to fetch coordinates: ' . $e->getMessage());
            return null;
        }
    }

    private function getCurrentWeather(float $lat, float $lon, ?int $locationId = null): ?array
    {
        $startTime = microtime(true);
        $endpoint = "{$this->baseUrl}/data/2.5/weather";

        try {
            $response = Http::withoutVerifying()->get($endpoint, [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                $response->status(),
                $responseTime,
                $response->successful() ? null : $response->body()
            );

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'temp' => $data['main']['temp'],
                    'humidity' => $data['main']['humidity'],
                    'pressure' => $data['main']['pressure'],
                    'wind_speed' => $data['wind']['speed'] ?? 0,
                    'condition' => $data['weather'][0]['description'] ?? 'Unknown',
                    'icon' => $data['weather'][0]['icon'] ?? '01d',
                ];
            }

            return null;
        } catch (\Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                500,
                $responseTime,
                $e->getMessage()
            );

            Log::error('Failed to fetch weather data: ' . $e->getMessage());
            return null;
        }
    }


    public function getForecastByLocationName(string $locationName): array
    {
        $location = Location::where('name', $locationName)->first();

        if ($location && $location->hasCoordinates()) {
            $latitude = $location->latitude;
            $longitude = $location->longitude;
            $locationId = $location->id;
        } else {
            $coordinates = $this->getCoordinates($locationName, null);

            if (!$coordinates) {
                throw new \Exception('Could not find coordinates for this location.');
            }

            if ($location) {
                $location->update([
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                ]);
            } else {
                $location = Location::create([
                    'name' => $locationName,
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                    'status' => 'active',
                ]);
            }

            $latitude = $coordinates['lat'];
            $longitude = $coordinates['lon'];
            $locationId = $location->id;
        }

        $forecastData = $this->getForecastData($latitude, $longitude, $locationId);

        if (!$forecastData) {
            throw new \Exception('Could not fetch forecast data.');
        }

        $forecasts = [];
        foreach ($forecastData as $data) {
            $forecast = Forecast::create([
                'location_id' => $locationId,
                'temperature' => $data['temp'],
                'humidity' => $data['humidity'],
                'pressure' => $data['pressure'],
                'wind_speed' => $data['wind_speed'],
                'condition' => $data['condition'],
                'icon' => $data['icon'],
                'forecast_time' => $data['forecast_time'],
                'fetched_at' => now(),
            ]);
            $forecasts[] = $forecast;
        }

        return [
            'location' => $location,
            'forecasts' => $forecasts,
        ];
    }


    private function getForecastData(float $lat, float $lon, ?int $locationId = null): ?array
    {
        $startTime = microtime(true);
        $endpoint = "{$this->baseUrl}/data/2.5/forecast";

        try {
            $response = Http::withoutVerifying()->get($endpoint, [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                $response->status(),
                $responseTime,
                $response->successful() ? null : $response->body()
            );

            if ($response->successful()) {
                $data = $response->json();
                $forecasts = [];

                foreach ($data['list'] as $item) {
                    $forecasts[] = [
                        'temp' => $item['main']['temp'],
                        'humidity' => $item['main']['humidity'],
                        'pressure' => $item['main']['pressure'],
                        'wind_speed' => $item['wind']['speed'] ?? 0,
                        'condition' => $item['weather'][0]['description'] ?? 'Unknown',
                        'icon' => $item['weather'][0]['icon'] ?? '01d',
                        'forecast_time' => $item['dt_txt'],
                    ];
                }

                return $forecasts;
            }

            return null;
        } catch (\Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiRequest(
                $locationId,
                $endpoint,
                500,
                $responseTime,
                $e->getMessage()
            );

            Log::error('Failed to fetch forecast data: ' . $e->getMessage());
            return null;
        }
    }

    private function logApiRequest(
        ?int $locationId,
        string $endpoint,
        int $statusCode,
        int $responseTime,
        ?string $errorMessage = null
    ): void {
        try {
            ApiRequest::create([
                'location_id' => $locationId,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTime,
                'error_message' => $errorMessage,
                'requested_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log API request: ' . $e->getMessage());
        }
    }
}

