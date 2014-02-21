<?php

namespace ride\library\cms\theme;

use ride\library\template\theme\Theme as TemplateTheme;

/**
 * Interface for a frontend theme
 */
interface Theme extends TemplateTheme {

    /**
     * Gets the display name of this theme
     * @return string
     */
    public function getDisplayName();

    /**
     * Checks if a region exists in this theme
     * @return boolean
     */
    public function hasRegion($region);

    /**
     * Gets the regions of this theme
     * @return array Array with the region name as key and as value
     */
    public function getRegions();

}