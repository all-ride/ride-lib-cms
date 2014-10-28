<?php

namespace ride\library\cms\expired\io;

/**
 * Interface for the input/output implementation of the expired routes
 */
interface ExpiredRouteIO {

    /**
     * Sets the expired routes to the data source
     * @param string $siteId Id of the site
     * @param array $routes Array with ExpiredRoute objects
     * @return null
     */
    public function setExpiredRoutes($siteId, array $routes);

    /**
     * Gets the expired routes from the data source
     * @param string $siteId Id of the site
     * @return array Array with ExpiredRoute objects
     */
    public function getExpiredRoutes($siteId);

}
