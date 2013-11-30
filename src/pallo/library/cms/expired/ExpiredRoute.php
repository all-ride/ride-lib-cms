<?php

namespace pallo\library\cms\expired;

/**
 * Data container for a expired route
 */
class ExpiredRoute {

    /**
     * The node id of the route
     * @var string
     */
    private $node;

    /**
     * The locale of the route
     * @var string
     */
    private $locale;

    /**
     * The path of the route
     * @var string
     */
    private $path;

    /**
     * The base URL of the route
     * @var string
     */
    private $baseUrl;

    /**
     * Constructs a new expired route
     * @param string $node Id of the node
     * @param string $locale Locale code of the route
     * @param string $path Expired path
     * @param string $baseUrl Base URL
     * @return null
     */
    public function __construct($node, $locale, $path, $baseUrl) {
        $this->node = $node;
        $this->locale = $locale;
        $this->path = $path;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Checks if the provided var is the same as this route
     * @param mixed $var
     * @return boolean
     */
    public function equals($var) {
        if (!$var instanceof self) {
            return false;
        }

        if ($var->node == $this->node && $var->locale == $this->locale && $var->path == $this->path && $var->baseUrl == $this->baseUrl) {
            return true;
        }

        return false;
    }

    /**
     * Gets the node
     * @return string Id of the node
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Gets the locale of the route
     * @return string Locale code
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Gets the path
     * @return string Expired path
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Gets the base URL
     * @return string Base URL
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

}