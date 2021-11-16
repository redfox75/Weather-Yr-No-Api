<?php

namespace Redfox75\WeatherYrNoApi;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Redfox75\WeatherYrNoApi\Util\Exception as WYNException;
use Redfox75\WeatherYrNoApi\Util\NotFoundException  as WYNNotFoundException ;
class FetchData
{

    /* CACHE */


    /**
     * @var CacheItemPoolInterface|null $cache The cache to use.
     */
    private $cache = null;
    /**
     * @var bool
     */
    private $wasCached = false;

    /**
     * @var string file/DB
     */
    public $cacheType = "file";
    /* FINE CACHE */

public $contentType = null;
    public $contentLenght = null;


    /**
     * minute retention for caching data
     */
    const MINUTESRETENTION = 60;

    /**
     * Constructs the OpenWeatherMap object.
     *
     * @param string $apiKey The OpenWeatherMap API key. Required.
     * @param ClientInterface $httpClient A PSR-18 compatible HTTP client implementation.
     * @param RequestFactoryInterface $httpRequestFactory A PSR-17 compatbile HTTP request factory implementation.
     * @param null|CacheItemPoolInterface $cache If set to null, caching is disabled. Otherwise this must be
     *                                                        a PSR-6 compatible cache instance.
     * @param int $ttl How long weather data shall be cached. Defaults to 10 minutes.
     *                                                        Only used if $cache is not null.
     *
     * @api
     */
    public function __construct($httpClient, $httpRequestFactory, $ttl = 600, $cacheType = 'file', $cache = null)
    {

        if (!is_numeric($ttl)) {
            throw new InvalidArgumentException('$ttl must be numeric.');
        }

        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->cacheType = $cacheType;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }





    /**
     * Build the url to fetch weather data from.
     *
     * @param string $url The url to prepend.
     * @param        $query
     * @param        $lang
     * @param        $mode
     *
     * @return bool|string The fetched url, false on failure.
     */
    private function buildUrl($url, $query=null, $lang=null, $mode=null)
    {

         $queryUrl = $this->buildQueryUrlParameter($query);

        $url = $url .(($queryUrl) ? '?'.$queryUrl :  '');//. "$queryUrl&units=$units&lang=$lang&mode=$mode&APPID=";

        return $url;
    }


    /**
     * Builds the query string for the url.
     *
     * @param mixed $query
     *
     * @return string The built query string for the url.
     *
     * @throws InvalidArgumentException If the query parameter is invalid.
     */
    private function buildQueryUrlParameter($query)
    {
        if($query) {
            switch ($query) {
                case is_array($query) && isset($query['lat']) && isset($query['lon']) && is_numeric($query['lat']) && is_numeric($query['lon']):
                    return "lat={$query['lat']}&lon={$query['lon']}";
                /*case is_array($query) && is_numeric($query[0]):
                    return 'id=' . implode(',', $query);
                case is_numeric($query):
                    return "id=$query";
                case is_string($query) && strpos($query, 'zip:') === 0:
                    $subQuery = str_replace('zip:', '', $query);
                    return 'zip=' . urlencode($subQuery);
                case is_string($query):
                    return 'q=' . urlencode($query);*/
                default:
                    throw new InvalidArgumentException('Error: $query has the wrong format. See the documentation of OpenWeatherMap::getWeather() to read about valid formats.');
            }
        }
         return "";
    }


    public function getData($functionCaller, $url, $link =null, $params =[], $type = 'json',$retention=null ){
        $retention  = $retention ?? self::MINUTESRETENTION;
        //$urlToFetch = $this->buildUrl($url.$link);
        $urlToFetch = $this->buildUrl($url, $params);
        $link = $link ?? $url;
        $answer =  $this->cacheOrFetchResult($functionCaller, $link, $urlToFetch,$retention);
        if($type == 'json'){
            $answer = $this->parseJson($answer);
        }
        return   $answer;
    }


    /**
     * @param string $answer The content returned by OpenWeatherMap.
     *
     * @return stdClass|array
     * @throws WYNException If the content isn't valid JSON.
     */
    private function parseJson($answer)
    {
        $json = json_decode($answer);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WYNException('Weather yr.no API returned an invalid json object. JSON error was: "' .
                $this->json_last_error_msg() . '". The retrieved json was: ' . $answer);
        }
        if (isset($json->message)) {
            throw new WYNException('An error occurred: ' . $json->message);
        }

        return $json;
    }

    private function json_last_error_msg()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        static $ERRORS = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        $error = json_last_error();
        return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
    }




    /**
     * Fetches the result or delivers a cached version of the result.
     *
     * @param string $url
     *
     * @return string
     */
    private function cacheOrFetchResult($functionName, $link, $url, $minutes = 60)
    {

        $linkToInsert = (!is_string($link)) ? serialize($link) :$link;
/* cache on db laravel */
//        $responseDB = ApiLogs::getLastInsert($functionName, $linkToInsert, $url, $minutes);
        /* end  cache on db laravel */

        if (isset($responseDB->results)) {
            $result =  $responseDB->results;
        } else {
            $response = $this->httpClient->sendRequest($this->httpRequestFactory->createRequest("GET", $url));
            $result = $response->getBody()->getContents();
            /* header part */
            $headerLineContentType = $response->getHeaderLine("Content-Type");
            $headerLineContentLenght = $response->getHeaderLine("Content-Length");
            $this->contentLenght = $headerLineContentLenght ?? null;
            $this->contentType = $headerLineContentType ?? null;

            if ($response->getStatusCode() !== 200) {
                if (false !== strpos($result, 'not found') && $response->getStatusCode() === 404) {
                    throw new WYNNotFoundException();
                }
                throw new WYNException('OpenWeatherMap returned a response with status code ' . $response->getStatusCode() . ' and the following content `' . $result . '`');
            }
            /* cache on db laravel */

         //   self::Log($functionName, $linkToInsert, $url,  $result);
            /* End cache on db laravel */

        }
        return $result;
    }
    /* cache laravel */
/*
    public static function Log($method, $params, $url, $response)
    {
        $apilogs = new ApiLogs();
        $apilogs->method = $method;
        $apilogs->params = $params;
        $apilogs->url = $url;

        $apilogs->results = $response;
        $apilogs->save();
    }
*/
    /* end cache laravel */

}