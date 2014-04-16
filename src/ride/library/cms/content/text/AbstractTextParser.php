<?php

namespace ride\library\cms\content\text;

use ride\library\cms\node\Node;

/**
 * Abstract implementation for a text parser
 */
abstract class AbstractTextParser implements TextParser {

    /**
     * Instance of the node
     * @var \ride\library\cms\node\Node
     */
    protected $node;

    /**
     * Code of the locale
     * @var string
     */
    protected $locale;

    /**
     * Base URL to the system
     * @var string
     */
    protected $baseUrl;

    /**
     * Sets the current node
     * @param \ride\library\cms\node\Node $node Current node
     * @return null
     */
    public function setNode(Node $node) {
        $this->node = $node;
    }

    /**
     * Gets the current node
     * @return \ride\library\cms\node\Node
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Sets the current locale
     * @param string $locale Code of the locale
     * @return null
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Gets the current locale
     * @return string Code of the locale
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Sets the base URL
     * @param string $baseURL Base URL
     * @return null
     */
    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Gets the base URL of the request
     * @return string Base URL
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

}
