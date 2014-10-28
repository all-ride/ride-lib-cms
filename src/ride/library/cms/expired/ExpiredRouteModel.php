<?php

namespace ride\library\cms\expired;

use ride\library\cms\expired\io\ExpiredRouteIO;

/**
 * Model for the expired routes
 */
class ExpiredRouteModel {

    /**
     * Implementation of the expired route input/output
     * @var \ride\library\cms\expired\io\ExpiredRouteIO
     */
    private $io;

    /**
     * Array with the loaded routes
     * @var array
     */
    private $routes;

    /**
     * Constructs a new expired path model
     * @param \ride\library\cms\expired\io\ExpiredRouteIO $io
     * @return null
     */
    public function __construct(ExpiredRouteIO $io) {
        $this->io = $io;
        $this->routes = array();
    }

    /**
     * Adds a expired route for the provided node
     * @param string $site Id of the site
     * @param string $node Id of the node
     * @param string $locale Code of the locale
     * @param string $path Expired path
     * @param string $baseUrl Base URL of the expired path
     * @return null
     */
    public function addExpiredRoute($site, $node, $locale, $path, $baseUrl) {
        $this->getExpiredRoutes($site);

        $expiredRoute = new ExpiredRoute($node, $locale, $path, $baseUrl);

        $found = false;
        foreach ($this->routes[$site] as $route) {
            if ($route->equals($expiredRoute)) {
                $found = true;

                break;
            }
        }

        if (!$found) {
            $this->routes[$site][] = $expiredRoute;
        }

        $this->io->setExpiredRoutes($site, $this->routes[$site]);
    }

    /**
     * Removes the expired routes for a path
     * @param string $site Id of the site
     * @param string $path Path to remove
     * @return null
     */
    public function removeExpiredRoutesByPath($site, $path) {
        $this->getExpiredRoutes($site);

        foreach ($this->routes[$site] as $index => $route) {
            if ($route->getPath() == $path) {
                unset($this->routes[$site][$index]);
            }
        }

        $this->io->setExpiredRoutes($site, $this->routes[$site]);
    }

    /**
     * Removes the expired routes for a node
     * @param string $site Id of the site
     * @param string| $node Id of the node or an array of ids
     * @return null
     */
    public function removeExpiredRoutesByNode($site, $node) {
        $this->getExpiredRoutes($site);

        if (!is_array($node)) {
            $node = array($node);
        }

        foreach ($node as $n) {
            foreach ($this->routes[$site] as $index => $route) {
                if ($route->getNode() == $n) {
                    unset($this->routes[$site][$index]);
                }
            }
        }

        $this->io->setExpiredRoutes($site, $this->routes[$site]);
    }

    /**
     * Gets all the expired routes
     * @return array Array with a ExpiredRoute object as value
     */
    public function getExpiredRoutes($site) {
        if (!isset($this->routes[$site])) {
            $this->routes[$site] = $this->io->getExpiredRoutes($site);
        }

        return $this->routes[$site];
    }

}
