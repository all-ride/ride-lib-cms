<?php

namespace pallo\library\cms\theme;

use pallo\library\template\theme\Theme as TemplateTheme;

/**
 * Interface for a frontend theme
 */
interface Theme extends TemplateTheme {

    /**
     * Checks if a region exists in this layout
     * @return boolean
     */
    public function hasRegion($region);

    /**
     * Gets the regions for this theme
     * @return array Array with the region name as key and as value
     */
    public function getRegions();

}