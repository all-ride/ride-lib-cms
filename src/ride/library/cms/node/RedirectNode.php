<?php

namespace ride\library\cms\node;

use ride\library\cms\node\type\RedirectNodeType;

/**
 * Node implementation for a redirect
 */
class RedirectNode extends Node {

    /**
     * Property key for the redirect node
     * @var string
     */
    const PROPERTY_NODE = 'redirect.node';

    /**
     * Property key for the redirect url
     * @var string
     */
    const PROPERTY_URL = 'redirect.url';

    /**
     * Constructs a new site node
     * @return null
     */
    public function __construct() {
        parent::__construct(RedirectNodeType::NAME);

        $this->defaultInherit = true;
    }

    /**
     * Sets the redirect URL for the provided locale
     * @param string $locale Code of the locale
     * @param string $url A URL
     * @return null
     */
    public function setRedirectUrl($locale, $url) {
        $this->set(self::PROPERTY_URL . '.' . $locale, $url, false);
    }

    /**
     * Gets the redirect URL for the provided locale
     * @param string $locale Code of the locale
     * @return string|null The redirect URL
     */
    public function getRedirectUrl($locale) {
        return $this->get(self::PROPERTY_URL . '.' . $locale);
    }

    /**
     * Sets the redirect node for the provided locale
     * @param string $locale Code of the locale
     * @param string $node Id of a node
     * @return null
     */
    public function setRedirectNode($locale, $node) {
        $this->set(self::PROPERTY_NODE . '.' . $locale, $node, false);
    }

    /**
     * Gets the redirect node for the provided locale
     * @param string $locale Code of the locale
     * @return string|null The id of the node
     */
    public function getRedirectNode($locale) {
        return $this->get(self::PROPERTY_NODE . '.' . $locale);
    }

}