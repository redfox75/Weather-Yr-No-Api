<?php

use Http\Factory\Guzzle\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Redfox75\WeatherYrNoApi\Util\Util;


require_once __DIR__ . '/bootstrap.php';



// You can use every PSR-17 compatible HTTP request factory
// and every PSR-18 compatible HTTP client.
$httpRequestFactory = new RequestFactory();
$httpClient = GuzzleAdapter::createWithConfig([]);

$wyn = new WeatherYrNoApi($httpClient, $httpRequestFactory);

// setup:
// step 1 download and extract all files in public/imagesWYN/icons/weather

$iconLegends = $wyn->setupIcons(0);
var_dump($iconLegends);
//$links = $wyn->getWeatherLinks();
//var_dump($links);