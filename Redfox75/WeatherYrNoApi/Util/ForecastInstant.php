<?php

namespace Redfox75\WeatherYrNoApi\Util;

class ForecastInstant
{
    public $time;
    public $timeRaw;
    public $air_pressure_at_sea_level;
    public $air_temperature;
    public $cloud_area_fraction;
    public $relative_humidity;
    public $wind_from_direction;
    public $wind_speed;


    public function __construct($time, $data)
    {
        $this->time = $time;
        $this->timeRaw = $time;

    $this->air_pressure_at_sea_level = property_exists($data, 'air_pressure_at_sea_level') ? $data->air_pressure_at_sea_level : null;
    $this->air_temperature = property_exists($data, 'air_temperature') ? $data->air_temperature : null;
    $this->cloud_area_fraction = property_exists($data, 'cloud_area_fraction') ? $data->cloud_area_fraction : null;
    $this->relative_humidity = property_exists($data, 'relative_humidity') ? $data->relative_humidity : null;
    $this->wind_from_direction = property_exists($data, 'wind_from_direction') ? $data->wind_from_direction : null;
    $this->wind_speed = property_exists($data, 'wind_speed') ? $data->wind_speed : null;
    }
}