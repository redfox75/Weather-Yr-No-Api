<?php

namespace Redfox75\WeatherYrNoApi;

use PharData;
use Redfox75\WeatherYrNoApi;
use Redfox75\WeatherYrNoApi\Util\Icons;
use Redfox75\WeatherYrNoApi\Util\Library;


class IconsApi
{
    /* Icons url */
    public $apiIconHealthz = "https://api.met.no/weatherapi/weathericon/2.0/healthz";

    public $apiIconZip = "https://api.met.no/weatherapi/weathericon/2.0/data";
    public $apiIconLegend ="https://api.met.no/weatherapi/weathericon/2.0/legends.";


    /**
     * @var null if null, the path will be __DIR__/tmp
     */
    private $tmpPath = null;

    /**
     * @var WeatherYrNoApi
     */
    private $wyn ;

    /*
     * @var array
     */
private $iconsLegends;
    /**
     * @param WeatherYrNoApi $wyn
     */
    public function __construct(WeatherYrNoApi $wyn)
    {
        $this->wyn = $wyn;
    }

    /**
     * @param int $step
     * step = 0 ALL
     * step = 1 from legend
     */
    public function setupIcons($step = 0)
    {
        if ($step == 0){
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
        $json = $this->wyn->fetchData->getData(__FUNCTION__, $url, $link, 'file');

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
            $file = $path .'/'. $fileName;
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

    public function fetchIconsLegends($format = "json"){
        $iconsLegends =[];
        $type = "txt";
        if ($format == "json"){
            $type = "json";
        }
        $url = $this->apiIconLegend.$type;
        $link = "weathericon/2.0/legend".$type;
        $json = $this->wyn->fetchData->getData(__FUNCTION__, $url, $link);
        foreach ($json as $iconLabel => $iconData){
            $iconsLegends[$iconLabel] = new Icons($iconLabel, $iconData);
        }
        $this->iconsLegends =$iconsLegends;

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