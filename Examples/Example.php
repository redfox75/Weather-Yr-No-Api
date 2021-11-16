<?php

use Http\Factory\Guzzle\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Redfox75\WeatherYrNoApi;


require_once __DIR__ . '/bootstrap.php';


$lat ="44.8985961";
$lon ="7.3425174";


// You can use every PSR-17 compatible HTTP request factory
// and every PSR-18 compatible HTTP client.
$httpRequestFactory = new RequestFactory();
$httpClient = GuzzleAdapter::createWithConfig([]);

$wyn = new WeatherYrNoApi($httpClient, $httpRequestFactory);

//var_dump($links);