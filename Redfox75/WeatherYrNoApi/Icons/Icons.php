<?php

namespace Redfox75\WeatherYrNoApi\Icons;

/**
 *
 */
class Icons
{

    /**
     * @var string
     */
    public $label;
    /**
     * @var array
     */
    public $variants;
    /**
     * @var string
     */
    public $desc_nb;
    /**
     * @var string
     */
    public $desc_en;
    /**
     * @var string
     */
    public $desc_nn;
    /**
     * @var string
     */
    public $old_id;

    /**
     * @param $label
     * @param $iconData
     */
    public function __construct($label, $iconData)
    {
        $this->label = $label;
        $this->variants = $iconData->variants;
        $this->desc_nb = $iconData->desc_nb;
        $this->desc_en = $iconData->desc_en;
        $this->desc_nn = $iconData->desc_nn;
        $this->old_id = $iconData->old_id;
    }
}