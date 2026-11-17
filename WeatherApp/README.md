# WeatherApp · minimal forecast viewer

A simple PHP weather interface displaying up to five days of forecast data from [Visual Crossing's Timeline Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-weather-api/).

## Setup

Requires PHP 8.1+ with **cURL** and a Visual Crossing API key.

From the repository root:

```bash
# macOS / Linux / Git Bash
export VISUAL_CROSSING_API_KEY=your_api_key_here
cd WeatherApp
php -S 127.0.0.1:8080
```

For **PowerShell** use `$env:VISUAL_CROSSING_API_KEY='your_api_key_here'` instead of `export`.

Open <http://127.0.0.1:8080/index.php>, enter a city such as `Thessaloniki, Greece` and submit.

## Changes

- No placeholder API key embedded in source code. The key is loaded from an environment variable; **never commit it**.
- Uses the official HTTPS Timeline Weather API with encoded city names and JSON response parsing, rather than fragile regular expressions on CSV.
- Enables TLS verification, disables redirects and applies timeouts and response-size limits.
- Escapes all displayed weather fields and validates the city input.
- Handles missing keys, API failures and invalid data without PHP error dumps.

Data availability, quotas and commercial permissions depend on the account and service terms. Never rely on a forecast as your only safety information.
