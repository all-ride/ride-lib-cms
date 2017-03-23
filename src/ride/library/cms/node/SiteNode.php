<?php

namespace ride\library\cms\node;

use ride\library\cms\node\type\SiteNodeType;

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
     * Property key for auto publish
     * @var string
     */
    const PROPERTY_AUTO_PUBLISH = 'autopublish';

    /**
     * Property key for the base url
     * @var string
     */
    const PROPERTY_BASE_URL = 'url';

    /**
     * Available revisions of this site
     * @var array
     */
    protected $revisions;

    /**
     * Array with the widget instance id as key and the widget id as value
     * @var array
     */
    protected $widgets;

    /**
     * Offset for the instance id of a new widget
     * @var integer
     */
    protected $widgetIdOffset;

    /**
     * Constructs a new site node
     * @return null
     */
    public function __construct() {
        parent::__construct(SiteNodeType::NAME);

        $this->defaultInherit = true;

        $this->set(self::PROPERTY_AUTO_PUBLISH, 0, true);
        $this->set(Node::PROPERTY_PUBLISH, 1, true);
        $this->set(Node::PROPERTY_SECURITY, Node::AUTHENTICATION_STATUS_EVERYBODY, true);

        $this->revisions = array();
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
     * Gets whether this site has a copy localization method
     * @return boolean
     */
    public function isLocalizationMethodCopy() {
        return $this->getLocalizationMethod() == self::LOCALIZATION_METHOD_COPY;
    }

    /**
     * Gets whether this site has a unique localization method
     * @return boolean
     */
    public function isLocalizationMethodUnique() {
        return $this->getLocalizationMethod() == self::LOCALIZATION_METHOD_UNIQUE;
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
     * Gets the locale of the site for the provided URL
     * @param string $baseUrl Base URL to resolve
     * @return string|null Locale code if the base URL is set for this site,
     * null otherwise
     */
    public function getLocaleForBaseUrl($baseUrl) {
        $properties = $this->getProperties(self::PROPERTY_BASE_URL);

        foreach ($properties as $key => $property) {
            if ($property->getValue() !== $baseUrl) {
                continue;
            }

            return str_replace(self::PROPERTY_BASE_URL . '.', '', $key);
        }

        return null;
    }

    /**
     * Checks îf this site has a different base URL for the different locales
     * @return boolean
     */
    public function hasLocalizedBaseUrl() {
        $baseUrl = null;

        $properties = $this->getProperties(self::PROPERTY_BASE_URL);

        foreach ($properties as $key => $property) {
            if ($baseUrl === null) {
                $baseUrl = $property->getValue();
            } elseif ($property->getValue() !== $baseUrl) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a proposal to prefix the route for a new node with
     * @param array $locales Array with the available locale code as key
     * @param string $locale Locale code
     * @return string Route prefix proposal
     */
    public function getRoutePrefixProposal(array $locales, $locale) {
        if (count($locales) == 1) {
            return '';
        }

        $countBaseUrls = array();
        $localeBaseUrls = array();

        $properties = $this->getProperties(self::PROPERTY_BASE_URL);

        foreach ($properties as $key => $property) {
            $baseUrl = $property->getValue();


            if (isset($baseUrls[$baseUrl])) {
                $countBaseUrls[$baseUrl]++;
            } else {
                $countBaseUrls[$baseUrl] = 1;
            }

            if ($key !== self::PROPERTY_BASE_URL) {
                $localeBaseUrls[str_replace(self::PROPERTY_BASE_URL . '.', '', $key)] = $baseUrl;
            }
        }

        if (isset($localeBaseUrls[$locale]) && $countBaseUrls[$localeBaseUrls[$locale]] == 1) {
            return '';
        }

        if (isset($countBaseUrls[$locale]) && $countBaseUrls[$locale] > 1) {
            $locale = explode('_', $locale);
            $locale = $locale[0];
        }

        return '/' . $locale;
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
     * Sets the available revisions of this site
     * @param array $revisions
     * @return null
     */
    public function setRevisions(array $revisions) {
        $this->revisions = $revisions;
    }

    /**
     * Gets the available revisions of this site
     * @return array
     */
    public function getRevisions() {
        return $this->revisions;
    }

    /**
     * Checks if this site has a certain revision
     * @param string $revision Name of the revision
     * @return boolean
     */
    public function hasRevision($revision) {
        return isset($this->revisions[$revision]);
    }

    /**
     * Sets whether this site will be auto published
     * @param boolean $îsAutoPublish
     * @return null
     */
    public function setIsAutoPublish($isAutoPublish) {
        $this->set(self::PROPERTY_AUTO_PUBLISH, $isAutoPublish ? 1 : 0);
    }

    /**
     * Gets whether this site will be auto published
     * @return boolean
     */
    public function isAutoPublish() {
        return $this->get(self::PROPERTY_AUTO_PUBLISH);
    }

    /**
     * Creates a widget instance
     * @param string $widgetId Id of the widget
     * @return integer Id of the new widget instance
     */
    public function createWidget($widgetId) {
        $instanceId = $this->getWidgetIdOffset();
        do {
            $instanceId++;
        } while (isset($this->properties[self::PROPERTY_WIDGET . '.' . $instanceId]));

        $this->set(self::PROPERTY_WIDGET . '.' . $instanceId, $widgetId, true);
        $this->widgets[$instanceId] = $widgetId;

        return $instanceId;
    }

    /**
     * Sets the offset for the widget id of a new widget instance
     * @param integer $widgetIdOffset
     * @return null
     */
    public function setWidgetIdOffset($widgetIdOffset) {
        $this->widgetIdOffset = $widgetIdOffset;
    }

    /**
     * Gets the offset for the widget id of a new widget instance
     * @return integer
     */
    public function getWidgetIdOffset() {
        if ($this->widgetIdOffset) {
            return $this->widgetIdOffset;
        }

        return 0;
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

    /**
     * Checks if this node is the homepage for the provided locale
     * @param string $locale Code of the locale
     * @return boolean
     */
    public function isHomepage($locale) {
        return false;
    }

}
