<?php

namespace Redfox75\WeatherYrNoApi\Util;

use finfo;

/**
 *
 */
class Utils
{
    /**
     * @param $file
     * @param string $fileTypeCheck
     * @return bool
     */
    public static function CheckFileType($file, $fileTypeCheck = 'gzip')
    {
        $isGivenTypeReturn = true;
        $fileInfo = new finfo();
        $fileType = $fileInfo->file($file);
        $isGivenType = stripos($fileType, $fileTypeCheck);
        if ($isGivenType === false) {
            $isGivenTypeReturn = false;
        }
        return $isGivenTypeReturn;

    }

    /**
     * @param $dirPath
     */
    public static function deleteDirectory($dirPath)
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        self::deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }
}