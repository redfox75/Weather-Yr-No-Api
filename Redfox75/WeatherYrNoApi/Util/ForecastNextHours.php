<?php

namespace Redfox75\WeatherYrNoApi\Util;

class ForecastNextHours
{
    public $time;
    public $timeRaw;
    public $symbolCode;
    public $precipitationAmount;
    public function __construct($time, $data)
    {
        $this->time = $time;
        $this->timeRaw = $time;
        if(property_exists($data, 'summary')) {
            $this->symbolCode =
                property_exists($data->summary, 'symbol_code') ? $data->summary->symbol_code : null;
        }
        else {
            $this->symbolCode = null;
        }

        if(property_exists($data, 'details')){
        $this->precipitationAmount =
            property_exists($data->details, 'precipitation_amount') ? $data->details->precipitation_amount : null;
        }
        else {
            $this->precipitationAmount = null;
        }
    }

    /*
     *           "next_1_hours": {
            "summary": {
              "symbol_code": "rain"
            },
            "details": {
              "precipitation_amount": 0.3
            }
          },
     */
}