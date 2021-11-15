<?php

namespace Redfox75;

use PharData;
use Redfox75\WeatherYrNoApi\FetchData;
use Redfox75\WeatherYrNoApi\Icons\Icons;
use Redfox75\WeatherYrNoApi\Util\Utils;
use RuntimeException;

class WeatherYrNoApi
{
    /**
     * @var FetchData
     */
    public $fetchData = null;
    public $apiBase = "https://www.yr.no";
    public $apiUrlLocation = "https://www.yr.no/api/v0/locations/2-3170694?language=en";
    public $apiUrlForecast = "https://www.yr.no/api/v0/locations/2-3170694/forecast";
    public $apiIconZip = "https://api.met.no/weatherapi/weathericon/2.0/data";
    public $apiIconLegend ="https://api.met.no/weatherapi/weathericon/2.0/legends.";
    /**
     * @var null if null, the path will be __DIR__/tmp
     */
    private $tmpPath = null;
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
     * @param int $step
     * step = 0 ALL
     * step = 1 from legend
     */
    public function setupIcons($step = 0)
    {
        if ($step == 0){
            $this->getIcons();
        }
        $iconLegends = $this->getIconsLegends();
        return $iconLegends;
    }

    /**
     * get icons zip and initialize the system
     */
    private function getIcons()
    {
        //set tmp path
        $this->tmpPath = $this->tmpPath ?? __DIR__ . '/tmp';
        //url to fetch icons zip
        $url = $this->apiIconZip;
        $link = "weathericon/2.0/data";

        $json = $this->fetchData->getData(__FUNCTION__, $url, $link, 'file');
        if ($this->fetchData->contentLenght > 0) {
            $contentTypeArray = explode(';', $this->fetchData->contentType);
            $fileNameArray = explode('=', $contentTypeArray[1]);
            $fileName = str_replace('"', '', $fileNameArray[1]);

            $path = $this->tmpPath . '/iconImages';

            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
            }
            $file = $path . $fileName;
            // Write the contents back to the file
            if (file_exists($file)) {
                unlink($file);
            }
            file_put_contents($file, $json);
            //Make it writeable
            chmod($file, 0777);

            $isArchive = Utils::CheckFileType($file, 'gzip');
            if ($isArchive) {
                $pathExtract = $path . "/extract";
                if (!is_dir($pathExtract)) {
                    if (!mkdir($pathExtract, 0777, true) && !is_dir($pathExtract)) {
                        throw new RuntimeException(sprintf('Directory "%s" was not created', $pathExtract));
                    }
                }


                /* extract files */
                try {
                    $phar = new PharData($file);
                    $phar->extractTo($pathExtract, null, true); // extract all files, and overwrite
                } catch (Exception $e) {
                    // handle errors
                }

                // directory destination
                $destination = __DIR__ . '/../public/imagesWYN/icons/weather';
                // clean destination directory
                Utils::deleteDirectory($destination);
                rename($pathExtract, $destination);
                // remove old temps files
                Utils::deleteDirectory($path);
            }
        }
    }

    public function getIconsLegends($format = "json"){
        $iconsLegends =[];
        $type = "txt";
        if ($format == "json"){
            $type = "json";
        }
        $url = $this->apiIconLegend.$type;
        $link = "weathericon/2.0/legend".$type;
        $json = $this->fetchData->getData(__FUNCTION__, $url, $link);
        foreach ($json as $iconLabel => $iconData){

            $iconsLegends[$iconLabel] = new Icons($iconLabel, $iconData);
        }

        return $iconsLegends;
    //
}

    /**
     * get the weather links, starting from location
     * @return mixed
     */
    public
    function getWeatherLinks()
    {
        $url = $this->apiUrlLocation;
        $link = null;
        $json = $this->fetchData->getData(__FUNCTION__, $url, null, null);
        return $json;
    }


}