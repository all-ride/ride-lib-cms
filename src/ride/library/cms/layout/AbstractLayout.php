<?php

namespace ride\library\cms\layout;

/**
 * Single column page layout
 */
abstract class AbstractLayout implements Layout {

    /**
     * Region names
     * @var array
     */
    protected $regions;

    /**
     * Gets the machine name of the layout
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Gets the template resource of the layout
     * @return string
     */
    public function getResource() {
        return 'cms/frontend/layout-' . static::NAME;
    }

    /**
     * Checks if a region exists in this layout
     * @return boolean
     */
    public function hasRegion($region) {
        return isset($this->regions[$region]);
    }

    /**
     * Gets the regions for this layout
     * @return array Array with the region name as key and as value
     */
    public function getRegions() {
        return $this->regions;
    }

}