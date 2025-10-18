<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Weather App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .weather-icon {
            width: 80px;
            height: 80px;
        }
        .forecast-table {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Weather Application</h1>

        <!-- Current Weather Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Current Weather</h5>
            </div>
            <div class="card-body">
                <form id="weatherForm" class="row g-3">
                    <div class="col-md-8">
                        <label for="locationInput" class="form-label">Location</label>
                        <input type="text" class="form-control" id="locationInput"
                               placeholder="Enter location name (e.g., London, New York)" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Get Weather</button>
                    </div>
                </form>

                <div id="weatherLoader" class="text-center mt-3 d-none">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="weatherAlert" class="alert alert-dismissible fade show mt-3 d-none" role="alert"></div>

                <div id="weatherResult" class="mt-4 d-none">
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h4 id="locationName"></h4>
                            <p class="text-muted" id="coordinates"></p>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <img id="weatherIcon" class="weather-icon" src="" alt="Weather icon">
                        </div>
                        <div class="col-md-3">
                            <h2 id="temperature" class="mb-0"></h2>
                            <p id="condition" class="text-capitalize"></p>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Humidity:</strong></td>
                                    <td id="humidity"></td>
                                </tr>
                                <tr>
                                    <td><strong>Pressure:</strong></td>
                                    <td id="pressure"></td>
                                </tr>
                                <tr>
                                    <td><strong>Wind Speed:</strong></td>
                                    <td id="windSpeed"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <p class="text-muted small" id="fetchedTime"></p>
                </div>
            </div>
        </div>

        <!-- Forecast Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">5-Day Forecast</h5>
            </div>
            <div class="card-body">
                <form id="forecastForm" class="row g-3">
                    <div class="col-md-8">
                        <label for="forecastLocationInput" class="form-label">Location</label>
                        <input type="text" class="form-control" id="forecastLocationInput"
                               placeholder="Enter location name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-success w-100">Get Forecast</button>
                    </div>
                </form>

                <div id="forecastLoader" class="text-center mt-3 d-none">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="forecastAlert" class="alert alert-dismissible fade show mt-3 d-none" role="alert"></div>

                <div id="forecastResult" class="mt-4 d-none">
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-striped forecast-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Temp (°C)</th>
                                    <th>Condition</th>
                                    <th>Humidity</th>
                                    <th>Pressure</th>
                                    <th>Wind</th>
                                </tr>
                            </thead>
                            <tbody id="forecastTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Forecasts</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label for="filterLocation" class="form-label">Location</label>
                        <select class="form-select" id="filterLocation">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ ucfirst($location->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="filterStartDate">
                    </div>
                    <div class="col-md-3">
                        <label for="filterEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="filterEndDate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-info w-100">Filter</button>
                    </div>
                </form>

                <div id="filterLoader" class="text-center mt-3 d-none">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="filterAlert" class="alert alert-dismissible fade show mt-3 d-none" role="alert"></div>

                <div id="filterResult" class="mt-4 d-none">
                    <hr>
                    <p><strong>Results:</strong> <span id="filterCount">0</span> records found</p>
                    <div class="table-responsive">
                        <table class="table table-striped forecast-table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Date/Time</th>
                                    <th>Temp (°C)</th>
                                    <th>Condition</th>
                                    <th>Humidity</th>
                                    <th>Pressure</th>
                                    <th>Wind</th>
                                </tr>
                            </thead>
                            <tbody id="filterTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        document.getElementById('weatherForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const location = document.getElementById('locationInput').value.trim();

            hideElement('weatherResult');
            hideAlert('weatherAlert');
            showLoader('weatherLoader');

            try {
                const response = await fetch('/weather', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ location: location })
                });

                const data = await response.json();
                hideLoader('weatherLoader');

                if (data.status === 'success') {
                    displayWeather(data.data);
                } else {
                    showAlert('weatherAlert', data.message, 'danger');
                }
            } catch (error) {
                hideLoader('weatherLoader');
                showAlert('weatherAlert', 'An error occurred. Please try again.', 'danger');
            }
        });

        document.getElementById('forecastForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const location = document.getElementById('forecastLocationInput').value.trim();

            hideElement('forecastResult');
            hideAlert('forecastAlert');
            showLoader('forecastLoader');

            try {
                const response = await fetch('/forecast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ location: location })
                });

                const data = await response.json();
                hideLoader('forecastLoader');

                if (data.status === 'success') {
                    displayForecast(data.data);
                } else {
                    showAlert('forecastAlert', data.message, 'danger');
                }
            } catch (error) {
                hideLoader('forecastLoader');
                showAlert('forecastAlert', 'An error occurred. Please try again.', 'danger');
            }
        });

        document.getElementById('filterForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const locationId = document.getElementById('filterLocation').value;
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;

            hideElement('filterResult');
            hideAlert('filterAlert');
            showLoader('filterLoader');

            const params = new URLSearchParams();
            if (locationId) params.append('location_id', locationId);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            try {
                const response = await fetch('/filter-forecasts?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                hideLoader('filterLoader');

                if (data.status === 'success') {
                    displayFilterResults(data.data);
                } else {
                    showAlert('filterAlert', data.message, 'danger');
                }
            } catch (error) {
                hideLoader('filterLoader');
                showAlert('filterAlert', 'An error occurred. Please try again.', 'danger');
            }
        });

        function displayWeather(data) {
            const { location, weather } = data;

            document.getElementById('locationName').textContent = location.name.charAt(0).toUpperCase() + location.name.slice(1);
            document.getElementById('coordinates').textContent = `${location.latitude}°N, ${location.longitude}°E`;
            document.getElementById('weatherIcon').src = weather.icon_url;
            document.getElementById('temperature').textContent = `${Math.round(weather.temperature)}°C`;
            document.getElementById('condition').textContent = weather.condition;
            document.getElementById('humidity').textContent = `${weather.humidity}%`;
            document.getElementById('pressure').textContent = `${weather.pressure} hPa`;
            document.getElementById('windSpeed').textContent = `${weather.wind_speed} m/s`;
            document.getElementById('fetchedTime').textContent = `Updated: ${weather.fetched_at}`;

            showElement('weatherResult');
        }

        function displayForecast(data) {
            const { location, forecasts } = data;
            const tbody = document.getElementById('forecastTableBody');
            tbody.innerHTML = '';

            forecasts.forEach(forecast => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${forecast.forecast_time}</td>
                    <td>${Math.round(forecast.temperature)}</td>
                    <td class="text-capitalize">${forecast.condition}</td>
                    <td>${forecast.humidity}%</td>
                    <td>${forecast.pressure} hPa</td>
                    <td>${forecast.wind_speed} m/s</td>
                `;
            });

            showElement('forecastResult');
        }

        function displayFilterResults(data) {
            document.getElementById('filterCount').textContent = data.count;
            const tbody = document.getElementById('filterTableBody');
            tbody.innerHTML = '';

            if (data.count === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No records found</td></tr>';
            } else {
                data.results.forEach(forecast => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${forecast.location.name}</td>
                        <td>${forecast.forecast_time}</td>
                        <td>${Math.round(forecast.temperature)}</td>
                        <td class="text-capitalize">${forecast.condition}</td>
                        <td>${forecast.humidity}%</td>
                        <td>${forecast.pressure} hPa</td>
                        <td>${forecast.wind_speed} m/s</td>
                    `;
                });
            }

            showElement('filterResult');
        }

        function showLoader(id) {
            document.getElementById(id).classList.remove('d-none');
        }

        function hideLoader(id) {
            document.getElementById(id).classList.add('d-none');
        }

        function showElement(id) {
            document.getElementById(id).classList.remove('d-none');
        }

        function hideElement(id) {
            document.getElementById(id).classList.add('d-none');
        }

        function showAlert(id, message, type) {
            const alert = document.getElementById(id);
            alert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alert.classList.remove('d-none');
        }

        function hideAlert(id) {
            document.getElementById(id).classList.add('d-none');
        }
    </script>
</body>
</html>
