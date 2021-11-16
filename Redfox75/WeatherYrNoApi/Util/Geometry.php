<?php

namespace Redfox75\WeatherYrNoApi\Util;

class Geometry
{
public $type;
public $coordinates;
public function __construct($data)
{
    $this->type = $data->type;
    $this->coordinates['longitude'] = $data->coordinates[0];
    $this->coordinates['latitude'] = $data->coordinates[1];
    $this->coordinates['altitude'] = $data->coordinates[2];
}

}