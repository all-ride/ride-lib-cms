<?php

namespace ride\library\cms\expired\io;

use ride\library\cms\expired\ExpiredRoute;
use ride\library\system\file\File;

use \DOMDocument;

/**
 * Xml implementation for the input/output of the expired routes
 */
class XmlExpiredRouteIO implements ExpiredRouteIO {

    /**
     * Name of the file in the site directory
     * @var string
     */
    const FILE = 'expired.xml';

    /**
     * Name of the root tag
     * @var string
     */
    const TAG_ROOT = 'expired';

    /**
     * Name of the route tag
     * @var string
     */
    const TAG_ROUTE = 'route';

    /**
     * Name of the path attribute
     * @var string
     */
    const ATTRIBUTE_PATH = 'path';

    /**
     * Name of the locale attribute
     * @var string
     */
    const ATTRIBUTE_LOCALE = 'locale';

    /**
     * Name of the base URL attribute
     * @var string
     */
    const ATTRIBUTE_BASE_URL = 'base';

    /**
     * Name of the node attribute
     * @var string
     */
    const ATTRIBUTE_NODE = 'node';

    /**
     * File to store the expired paths
     * @var \ride\library\system\file\File
     */
    private $directory;

    /**
     * Constructs a new expired route IO
     * @param \ride\library\system\file\File $file
     * @return null
     */
    public function __construct(File $directory) {
        $this->directory = $directory;
    }

    /**
     * Sets the expired routes to the data source
     * @param string $site Id of the site
     * @param array $routes Array with ExpiredRoute objects
     * @return null
     */
    public function setExpiredRoutes($site, array $routes) {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $expiredElement = $dom->createElement(self::TAG_ROOT);
        $dom->appendChild($expiredElement);

        foreach ($routes as $route) {
            $node = $route->getNode();
            $locale = $route->getLocale();
            $path = $route->getPath();
            $baseUrl = $route->getBaseUrl();

            $routeElement = $dom->createElement(self::TAG_ROUTE);
            $routeElement->setAttribute(self::ATTRIBUTE_NODE, $node);
            $routeElement->setAttribute(self::ATTRIBUTE_LOCALE, $locale);
            $routeElement->setAttribute(self::ATTRIBUTE_PATH, $path);
            if ($baseUrl) {
                $routeElement->setAttribute(self::ATTRIBUTE_BASE_URL, $baseUrl);
            }

            $importedRouteElement = $dom->importNode($routeElement, true);
            $expiredElement->appendChild($importedRouteElement);
        }

        $file = $this->directory->getChild($site . '/' . self::FILE);
        $file->getParent()->create();

        $dom->save($file);
    }

    /**
     * Gets the expired routes from the data source
     * @param string $site Id of the site
     * @return array Array with ExpiredRoute objects
     */
    public function getExpiredRoutes($site) {
        $routes = array();

        $file = $this->directory->getChild($site . '/' . self::FILE);
        if (!$file->exists()) {
            return $routes;
        }

        $dom = new DOMDocument();
        $dom->load($file);

        foreach ($dom->documentElement->childNodes as $element) {
            if ($element->nodeName != self::TAG_ROUTE) {
                continue;
            }

            $node = $element->getAttribute(self::ATTRIBUTE_NODE);
            $locale = $element->getAttribute(self::ATTRIBUTE_LOCALE);
            $path = $element->getAttribute(self::ATTRIBUTE_PATH);
            $baseUrl = $element->getAttribute(self::ATTRIBUTE_BASE_URL);

            $routes[] = new ExpiredRoute($node, $locale, $path, $baseUrl);
        }

        return $routes;
    }

}
