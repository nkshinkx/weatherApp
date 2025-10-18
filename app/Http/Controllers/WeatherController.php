<?php

namespace App\Http\Controllers;

use App\Models\Forecast;
use App\Models\Location;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index()
    {
        $locations = Location::where('status', 'active')->whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get();
        return view('weather.index', compact('locations'));
    }

    public function getWeather(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|min:2|max:150',
        ]);

        if ($validator->fails()) {
            return $this->error('Please enter a valid location name.', 422);
        }

        try {
            $locationName = trim($request->input('location'));
            $data = $this->weatherService->getWeatherByLocationName($locationName);

            return $this->success([
                'location' => [
                    'name' => $data['location']->name,
                    'latitude' => $data['location']->latitude,
                    'longitude' => $data['location']->longitude,
                ],
                'weather' => [
                    'temperature' => $data['weather']->temperature,
                    'humidity' => $data['weather']->humidity,
                    'pressure' => $data['weather']->pressure,
                    'wind_speed' => $data['weather']->wind_speed,
                    'condition' => $data['weather']->condition,
                    'icon' => $data['weather']->icon,
                    'icon_url' => $data['weather']->icon_url,
                    'fetched_at' => $data['weather']->fetched_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function getForecast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|min:2|max:150',
        ]);

        if ($validator->fails()) {
            return $this->error('Please enter a valid location name.', 422);
        }

        try {
            $locationName = trim($request->input('location'));
            $data = $this->weatherService->getForecastByLocationName($locationName);

            $forecasts = [];
            foreach ($data['forecasts'] as $forecast) {
                $forecasts[] = [
                    'temperature' => $forecast->temperature,
                    'humidity' => $forecast->humidity,
                    'pressure' => $forecast->pressure,
                    'wind_speed' => $forecast->wind_speed,
                    'condition' => $forecast->condition,
                    'icon' => $forecast->icon,
                    'icon_url' => $forecast->icon_url,
                    'forecast_time' => $forecast->forecast_time->format('Y-m-d H:i:s'),
                ];
            }

            return $this->success([
                'location' => [
                    'name' => $data['location']->name,
                    'latitude' => $data['location']->latitude,
                    'longitude' => $data['location']->longitude,
                ],
                'forecasts' => $forecasts,
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function filterForecasts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'nullable|exists:locations,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid filter parameters.', 422);
        }

        try {
            $query = Forecast::with('location');

            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->filled('start_date')) {
                $query->where('forecast_time', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->where('forecast_time', '<=', $request->end_date . ' 23:59:59');
            }

            $forecasts = $query->orderBy('forecast_time')->get();

            $results = $forecasts->map(function ($forecast) {
                return [
                    'location' => [
                        'id' => $forecast->location->id,
                        'name' => $forecast->location->name,
                    ],
                    'temperature' => $forecast->temperature,
                    'humidity' => $forecast->humidity,
                    'pressure' => $forecast->pressure,
                    'wind_speed' => $forecast->wind_speed,
                    'condition' => $forecast->condition,
                    'icon_url' => $forecast->icon_url,
                    'forecast_time' => $forecast->forecast_time->format('Y-m-d H:i:s'),
                ];
            });

            return $this->success([
                'count' => $results->count(),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}

