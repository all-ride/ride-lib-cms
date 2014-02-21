<?php

namespace ride\library\cms\node;

use ride\library\cms\node\type\PageNodeType;

/**
 * Node implementation for a page
 */
class PageNode extends Node {

    /**
     * Property key for the layout
     * @var string
     */
    const PROPERTY_LAYOUT = 'layout';

    /**
     * Constructs a new site node
     * @return null
     */
    public function __construct() {
        parent::__construct(PageNodeType::NAME);

        $this->defaultInherit = false;
    }

    /**
     * Sets the redirect URL for the provided locale
     * @param string $locale Code of the locale
     * @param string $url A URL
     * @return null
     */
    public function setLayout($locale, $layout) {
        $this->set(self::PROPERTY_LAYOUT . '.' . $locale, $layout, false);
    }

    /**
     * Gets the layout
     * @param string $locale Code of the locale
     * @return string|null Machine name of the layout
     */
    public function getLayout($locale) {
        if ($locale) {
            $layout = $this->get(self::PROPERTY_LAYOUT . '.' . $locale);
            if ($layout) {
                return $layout;
            }
        }

        foreach ($this->properties as $key => $value) {
            if ($key == self::PROPERTY_LAYOUT || strpos($key, self::PROPERTY_LAYOUT . '.') === 0) {
                return $value->getValue();
            }
        }

        return null;
    }

}