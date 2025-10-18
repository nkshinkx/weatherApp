<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'index'])->name('weather.index');
Route::post('/weather', [WeatherController::class, 'getWeather'])->name('weather.get');
Route::post('/forecast', [WeatherController::class, 'getForecast'])->name('weather.forecast');
Route::get('/filter-forecasts', [WeatherController::class, 'filterForecasts'])->name('weather.filter');
