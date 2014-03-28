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
        $this->routes = null;
    }

    /**
     * Adds a expired route for the provided node
     * @param string $node Id of the node
     * @param string $locale Code of the locale
     * @param string $path Expired path
     * @param string $baseUrl Base URL of the expired path
     * @return null
     */
    public function addExpiredRoute($node, $locale, $path, $baseUrl) {
        if ($this->routes === null) {
            $this->routes = $this->io->getExpiredRoutes();
        }

        $expiredRoute = new ExpiredRoute($node, $locale, $path, $baseUrl);

        $found = false;
        foreach ($this->routes as $route) {
            if ($route->equals($expiredRoute)) {
                $found = true;

                break;
            }
        }

        if (!$found) {
            $this->routes[] = $expiredRoute;
        }

        $this->io->setExpiredRoutes($this->routes);
    }

    /**
     * Removes the expired routes for a path
     * @param string $path Path to remove
     * @return null
     */
    public function removeExpiredRoutesByPath($path) {
        $this->getExpiredRoutes();

        foreach ($this->routes as $index => $route) {
            if ($route->getPath() == $path) {
                unset($this->routes[$index]);
            }
        }

        $this->io->setExpiredRoutes($this->routes);
    }

    /**
     * Removes the expired routes for a node
     * @param string $node Id of the node
     * @return null
     */
    public function removeExpiredRoutesByNode($node) {
        $this->getExpiredRoutes();

        foreach ($this->routes as $index => $route) {
            if ($route->getNode() == $node) {
                unset($this->routes[$index]);
            }
        }

        $this->io->setExpiredRoutes($this->routes);
    }

    /**
     * Gets all the expired routes
     * @return array Array with a ExpiredRoute object as value
     */
    public function getExpiredRoutes() {
        if ($this->routes === null) {
            $this->routes = $this->io->getExpiredRoutes();
        }

        return $this->routes;
    }

}