<?php

namespace Redfox75\WeatherYrNoApi\Util;

/**
 *
 */
class Units
{
    /**
     * @var
     */
    public $air_pressure_at_sea_level;
    /**
     * @var
     */
    public $air_temperature;
    /**
     * @var
     */
    public $cloud_area_fraction;
    /**
     * @var
     */
    public $precipitation_amount;
    /**
     * @var
     */
    public $relative_humidity;
    /**
     * @var
     */
    public $wind_from_direction;
    /**
     * @var
     */
    public $wind_speed;
    /**
     * @var
     */
    public $updated_at;
    /**
     * @var
     */
    public $updated_atRaw;


    /**
     * @param mixed $properties
     */
    public function __construct( $properties) {
        $this->updated_atRaw = $properties->updated_at;
/* TODO convert date*/

        $this->updated_at = $properties->updated_at;
        $units = $properties->units;

        $this->air_pressure_at_sea_level = $units->air_pressure_at_sea_level;
        $this->air_temperature = $units->air_temperature;
        $this->cloud_area_fraction = $units->cloud_area_fraction;
        $this->precipitation_amount = $units->precipitation_amount;
        $this->relative_humidity = $units->relative_humidity;
        $this->wind_from_direction = $units->wind_from_direction;
        $this->wind_speed = $units->wind_speed;

    }

}