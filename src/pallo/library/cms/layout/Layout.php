<?php

namespace pallo\library\cms\layout;

/**
 * Interface for a predefined page layout
 */
interface Layout {

    /**
     * Gets the machine name of the layout
     * @return string
     */
    public function getName();

    /**
     * Gets the template resource of the layout
     * @return string
     */
    public function getResource();

    /**
     * Checks if a region exists in this layout
     * @return boolean
     */
    public function hasRegion($region);

    /**
     * Gets the regions for this layout
     * @return array Array with the region name as key and as value
     */
    public function getRegions();

}