<?php

use Http\Factory\Guzzle\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Redfox75\WeatherYrNoApi;
use Redfox75\WeatherYrNoApi\LocationForecastApi;


require_once __DIR__ . '/bootstrap.php';


$lat = "44.8985961";
$lon = "7.3425174";
// $userAgent set by ini file

// You can use every PSR-17 compatible HTTP request factory
// and every PSR-18 compatible HTTP client.
$httpRequestFactory = new RequestFactory();

$httpClientConfig = [
    'headers' => [
        'User-Agent' => $userAgent
    ]
];

$httpClient = GuzzleAdapter::createWithConfig($httpClientConfig);

$wyn = new WeatherYrNoApi($httpClient, $httpRequestFactory);

$locationForecast = new LocationForecastApi($wyn, $lat, $lon);
$forecastCompact = $locationForecast->fetchCompact();

var_dump($forecastCompact);