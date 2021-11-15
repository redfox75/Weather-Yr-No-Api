<?php

namespace Redfox75;

use PharData;
use Redfox75\WeatherYrNoApi\FetchData;
use RuntimeException;

class WeatherYrNoApi
{
    /**
     * @var FetchData
     */
    public $fetchData = null;

    /**
     * @var int
     */
    private $ttl;
    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private $httpRequestFactory;

    /**
     * @param $httpClient
     * @param $httpRequestFactory
     * @param int $ttl
     */
    public function __construct($httpClient, $httpRequestFactory, $ttl = 600)
    {
        if (!is_numeric($ttl)) {
            throw new InvalidArgumentException('$ttl must be numeric.');
        }
        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->ttl = $ttl;
        $this->fetchData = new FetchData($httpClient, $httpRequestFactory, $ttl);
    }



    /**
     * get the weather links, starting from location
     * @return mixed
     */
    public function getWeatherLinks()
    {
        $url = $this->apiUrlLocation;
        $link = null;
        $json = $this->fetchData->getData(__FUNCTION__, $url, null, null);
        return $json;
    }


}