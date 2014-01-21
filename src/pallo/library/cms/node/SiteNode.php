<?php

namespace pallo\library\cms\node;

use pallo\library\cms\node\type\SiteNodeType;

/**
 * Node implementation for a site
 */
class SiteNode extends Node {

    /**
     * Localization method to keep a translated copy (1 tree)
     * @var string
     */
    const LOCALIZATION_METHOD_COPY = 'copy';

    /**
     * Localization method to keep a unique tree per locale
     * @var string
     */
    const LOCALIZATION_METHOD_UNIQUE = 'unique';

    /**
     * Property key for the localization method
     * @var string
     */
    const PROPERTY_LOCALIZATION_METHOD = 'l10n';

    /**
     * Property key for the base url
     * @var string
     */
    const PROPERTY_BASE_URL = 'url';

    /**
     * Array with the widget instance id as key and the widget id as value
     * @var array
     */
    protected $widgets;

    /**
     * Constructs a new site node
     * @return null
     */
    public function __construct() {
        parent::__construct(SiteNodeType::NAME);

        $this->defaultInherit = true;

        $this->set(Node::PROPERTY_PUBLISH, 1, true);

        $this->widgets = array();
    }

    /**
     * Sets the localization method of this site
     * @param string $method Localization method of this site
     * @return null
     */
    public function setLocalizationMethod($method) {
        return $this->set(self::PROPERTY_LOCALIZATION_METHOD, $method);
    }

    /**
     * Gets the localization method of this site
     * @return string
     */
    public function getLocalizationMethod() {
        return $this->get(self::PROPERTY_LOCALIZATION_METHOD, self::LOCALIZATION_METHOD_COPY);
    }

    /**
     * Sets the base URL for the provided locale
     * @param string $locale Code of the locale
     * @param string $url Base URL for this site
     * @return null
     */
    public function setBaseUrl($locale, $url) {
        $this->set(self::PROPERTY_BASE_URL . '.' . $locale, $url);
    }

    /**
     * Gets the base URL of this site for the provided locale
     * @param string $locale Code of the locale
     * @return string|null Base URL for this site
     */
    public function getBaseUrl($locale) {
        return $this->get(self::PROPERTY_BASE_URL . '.' . $locale);
    }

    /**
     * Sets a property
     * @param NodeProperty $property
     * @return null
     */
    public function setProperty(NodeProperty $property) {
        parent::setProperty($property);

        $key = $property->getKey();
        if (strpos($key, Node::PROPERTY_WIDGET) === 0 && strrpos($key, '.') === 6) {
            $this->widgets[substr($key, 7)] = $property->getValue();
        }
    }

    /**
     * Creates a widget instance
     * @param string $widgetId Id of the widget
     * @return integer Id of the new widget instance
     */
    public function createWidget($widgetId) {
        $instanceId = 0;
        do {
            $instanceId++;
        } while (isset($this->properties[self::PROPERTY_WIDGET . '.' . $instanceId]));

        $this->set(self::PROPERTY_WIDGET . '.' . $instanceId, $widgetId, true);
        $this->widgets[$instanceId] = $widgetId;

        return $instanceId;
    }

    /**
     * Gets the widget instance array
     * @return array Array with the widget id as key and the dependency id
     * as value
     */
    public function getAvailableWidgets() {
        return $this->widgets;
    }

    /**
     * Sets the widget instance array
     * @param array $widgets Array with the widget instance id as key and the
     * widget id as value
     * @return null
     */
    public function setAvailableWidgets(array $widgets) {
        $this->widgets = $widgets;
    }

    /**
     * Get the route of this node. The route is used in the frontend as an url
     * alias.
     * @param string $locale Code of the locale
     * @param boolean $returnDefault Set to false to return null when the route
     * is not set
     * @return string
     */
    public function getRoute($locale, $returnDefault = true) {
        return '/';
    }

}