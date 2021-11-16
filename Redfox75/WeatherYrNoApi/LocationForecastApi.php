<?php

namespace Redfox75\WeatherYrNoApi;

use PharData;
use Redfox75\WeatherYrNoApi;
use Redfox75\WeatherYrNoApi\Util\ForecastInstant;
use Redfox75\WeatherYrNoApi\Util\ForecastNextHours;
use Redfox75\WeatherYrNoApi\Util\Geometry;
use Redfox75\WeatherYrNoApi\Util\Icons;
use Redfox75\WeatherYrNoApi\Util\Library;
use Redfox75\WeatherYrNoApi\Util\Units;
use stdClass;


class LocationForecastApi
{
    /* Icons url */
    public $apiLocationHealthz = "https://api.met.no/weatherapi/locationforecast/2.0/healthz";
    public $apiLocationSchema = "https://api.met.no/weatherapi/locationforecast/2.0/schema";
    public $apiLocationCompact = "https://api.met.no/weatherapi/locationforecast/2.0/compact.json";

    /*
    https://api.met.no/weatherapi/locationforecast/2.0/compact.json?altitude=450&lat=44.8985961&lon=7.3425174





    GET /compact.{format}
    GET /complete.{format}
    GET /classic.{format}
    GET /status.{format}
    GET /complete
    GET /classic
    GET /compact
    GET /status
    [ base url: /weatherapi/loc
    */

    public $lat;
    public $lon;
    public $altitude;
    public $params;


    /**
     * @var WeatherYrNoApi
     */
    private $wyn;


    public function __construct(WeatherYrNoApi $wyn, string $lat, $lon, string $altitude = null)
    {
        $this->wyn = $wyn;
        $this->lat = $lat;
        $this->lon = $lon;
        $this->altitude = $altitude;
        $this->params = [];
        if ($this->altitude) {
            $this->params['altitude'] = $this->altitude;
        }
        $this->params['lat'] = $this->lat;
        $this->params['lon'] = $this->lon;

    }

    public function fetchCompact($format = "json")
    {
        $iconsLegends = [];
        $type = "txt";
        if ($format == "json") {
            $type = "json";
        }
        $url = $this->apiLocationCompact;
        $link = "weathericon/2.0/legend";

        $json = $this->wyn->fetchData->getData(__FUNCTION__, $url, $link, $this->params);

        $forecast =[];

        $geometry = new Geometry($json->geometry);
        $unit = new Units($json->properties->meta);
        $timeseries = [];
        foreach ($json->properties->timeseries as $k => $timeserie) {
            // $timeseries[$timeserie->time] = $timeserie;
            $time = $timeserie->time;
            $instantSingle =
                (property_exists($timeserie->data, 'instant')) ?
                    $timeserie->data->instant : new stdClass();
            $instant[$time] = new ForecastInstant($time, $instantSingle);
            $next1hoursSingle = (property_exists($timeserie->data, 'next_1_hours')) ? $timeserie->data->next_1_hours : new stdClass();
            $next6hoursSingle = (property_exists($timeserie->data, 'next_6_hours')) ? $timeserie->data->next_6_hours : new stdClass();
            $next12hoursSingle = (property_exists($timeserie->data, 'next_12_hours')) ? $timeserie->data->next_12_hours : new stdClass();
            $next1hours[$time] = new ForecastNextHours($time, $next1hoursSingle);
            $next6hours[$time] = new ForecastNextHours($time, $next6hoursSingle);
            $next12hours[$time] = new ForecastNextHours($time, $next12hoursSingle);
            $timeseries[$time] = [
                'instant' => $instant[$time],
                'next1hours' => $next1hours[$time],
                'next6hours' => $next6hours[$time],
                'next12hours' => $next12hours[$time],
            ];
        }
        $forecast['geometry'] = $geometry;
        $forecast['unit'] = $unit;
        $forecast['timeseries'] = $timeseries;
        return $forecast;
    }

    /**
     * @param int $step
     * step = 0 ALL
     * step = 1 from legend
     */
    public function setupIcons($step = 0)
    {
        if ($step == 0) {
            $this->fetchIcons();
        }
        $this->fetchIconsLegends();
    }

    /**
     * get icons zip and initialize the system
     */
    private function fetchIcons()
    {
        //set tmp path
        $this->tmpPath = $this->tmpPath ?? __DIR__ . '/tmp';

        //url to fetch icons zip
        $url = $this->apiIconZip;
        $link = "weathericon/2.0/data";
        $json = $this->wyn->fetchData->getData(__FUNCTION__, $url, $link, [], 'file');

        if ($this->wyn->fetchData->contentLenght > 0) {

            $contentTypeArray = explode(';', $this->wyn->fetchData->contentType);
            $fileNameArray = explode('=', $contentTypeArray[1]);
            $fileName = str_replace('"', '', $fileNameArray[1]);
            $this->tmpPath = '/temporary';
            $path = $this->tmpPath . '/iconImages';

            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
            }
            $file = $path . '/' . $fileName;
            // Write the contents back to the file
            if (file_exists($file)) {
                unlink($file);
            }
            file_put_contents($file, $json);
            //Make it writeable
            chmod($file, 0777);
            /* free memory, I've altreay saved al data in file */
            unset($json);

            $isArchive = Library::CheckFileType($file, 'gzip');
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
                // ../../ because is 2 level under
                $destination = __DIR__ . '/../../public/imagesWYN/icons/weather';
                // clean destination directory
//                Library::deleteDirectory($destination);
                rename($pathExtract, $destination);
                // remove old temps files
//                Library::deleteDirectory($path);
            }
        }
    }

    public function fetchIconsLegends($format = "json")
    {
        $iconsLegends = [];
        $type = "txt";
        if ($format == "json") {
            $type = "json";
        }
        $url = $this->apiIconLegend . $type;
        $link = "weathericon/2.0/legend" . $type;
        $json = $this->wyn->fetchData->getData(__FUNCTION__, $url, $link);
        foreach ($json as $iconLabel => $iconData) {
            $iconsLegends[$iconLabel] = new Icons($iconLabel, $iconData);
        }
        $this->iconsLegends = $iconsLegends;

    }

    /**
     * @return mixed
     */
    public function getIconsLegends()
    {
        return $this->iconsLegends;
    }

    /**
     * @param mixed $iconsLegends
     */
    public function setIconsLegends($iconsLegends)
    {
        $this->iconsLegends = $iconsLegends;
    }


}