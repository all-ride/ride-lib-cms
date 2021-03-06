<?php

namespace ride\library\cms\widget;

use ride\library\cms\exception\CmsException;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeProperty;
use ride\library\security\exception\AuthenticationException;
use ride\library\security\SecurityManager;
use ride\library\widget\WidgetProperties;
use ride\library\reflection\Boolean;

use \DateTime;

/**
 * Widget properties based on a node
 */
class NodeWidgetProperties implements WidgetProperties {

    /**
     * Automatic caching
     * @var string
     */
    const CACHE_AUTO =  'auto';

    /**
     * Enable caching
     * @var string
     */
    const CACHE_ENABLED =  'enabled';

    /**
     * Disable caching
     * @var string
     */
    const CACHE_DISABLED =  'disabled';

    /**
     * Property name for the cache enable flag
     * @var string
     */
    const PROPERTY_CACHE = 'cache';

    /**
     * Property name for the cache ttl
     * @var string
     */
    const PROPERTY_CACHE_TTL = 'cache.ttl';

    /**
     * id of the widget for who this container acts
     * @var integer
     */
	protected $widgetId;

	/**
	 * Prefix of the key for the WidgetProperty methods (widget.[widgetId].)
	 * @var string
	 */
	protected $widgetPropertyPrefix;

	/**
	 * Node for these properties
	 * @var ride\library\cms\node\Node
	 */
	protected $node;

	/**
     * Construct this setting container
     * @param ride\library\cms\node\Node $node Node which holds the widget
     * @param integer $widgetId Id of the widget instance
     * @return null
	 */
	public function __construct(Node $node, $widgetId) {
	    $this->widgetId = $widgetId;
	    $this->widgetPropertyPrefix = Node::PROPERTY_WIDGET . '.' . $this->widgetId . '.';
		$this->node = $node;
	}

	/**
	 * Gets the node
     * @return \ride\library\cms\node\Node
	 */
	public function getNode() {
	    return $this->node;
	}

	/**
	 * Get the id of the widget instance
	 * @return integer
	 */
	public function getWidgetId() {
	    return $this->widgetId;
	}

	/**
	 * Sets a property for the widget
	 * @param string $key Key of the property relative to widget.[widgetId].
	 * @param mixed $value Value for the property
	 * @return null
	 */
	public function setWidgetProperty($key, $value = null) {
	    $this->node->set($this->widgetPropertyPrefix . $key, $value);
	}

	/**
	 * Get a setting value for the widget
	 * @param string $key key of the setting
	 * @param mixed $default default value for when the setting is not set
	 * @return mixed setting value of $default if the setting was not set
	 */
	public function getWidgetProperty($key, $default = null) {
	    return $this->node->get($this->widgetPropertyPrefix . $key, $default);
	}

    /**
     * Gets all the properties of the widget
     * @param string $prefix Prefix of the properties to obtain
     * @return array Array with the properties of the widget
     */
    public function getWidgetProperties($prefix = null) {
        $prefix = $this->widgetPropertyPrefix . $prefix;
        $result = array();

        $properties = $this->node->getProperties($prefix);
        foreach ($properties as $key => $property) {
            $result[str_replace($this->widgetPropertyPrefix, '', $key)] = $property->getValue();
        }

        return $result;
    }

    /**
     * Clears all the properties of the widget
     * @param string $prefix Prefix of the properties to remove
     * @return null
     */
    public function clearWidgetProperties($prefix = null) {
        $prefix = $this->widgetPropertyPrefix . $prefix;

        $properties = $this->node->getProperties();

        foreach ($properties as $key => $property) {
            if (strpos($key, $prefix) === 0) {
                unset($properties[$key]);
            }
        }

        $this->node->setProperties($properties);
	}

	/**
	 * Gets a localized property from the widget
     * @param string $locale Code of the locale
	 * @param string $key Key of the property relative to widget.[widgetId]
	 * @param mixed $default default value for when the property is not set
 	 * @return mixed Property value or $default if the property was not set
	 */
	public function getLocalizedWidgetProperty($locale, $key, $default = null) {
	    return $this->node->getLocalized($locale, $this->widgetPropertyPrefix . $key, $default);
	}

	/**
	 * Sets a localized property to the widget
     * @param string $locale Code of the locale
	 * @param string $key Key of the property relative to widget.[widgetId]
	 * @param mixed $value Value to set
 	 * @return
	 */
    public function setLocalizedWidgetProperty($locale, $key, $value) {
        $this->node->setLocalized($locale, $this->widgetPropertyPrefix . $key, $value);
    }

	/**
	 * Sets the cache type
	 * @param string $type
	 * @return null
	 * @throws \ride\library\cms\exception\CmsException when an invalid cache
	 * type has been provided
	 */
	public function setCache($type = null) {
	    if ($type !== null && $type !== true && $type !== false && $type != self::CACHE_AUTO && $type != self::CACHE_DISABLED && $type != self::CACHE_ENABLED) {
	        throw new CmsException('Invalid cache type provided, try ' . self::CACHE_AUTO, ', ' . self::CACHE_DISABLED . ' or ' . self::CACHE_ENABLED);
	    }

	    if ($type === true) {
	        $type = self::CACHE_ENABLED;
	    } elseif ($type === false) {
	        $type = self::CACHE_DISABLED;
	    } elseif ($type === self::CACHE_AUTO) {
	        $type = null;
	    }

	    $this->setWidgetProperty(self::PROPERTY_CACHE, $type);
	}

	/**
	 * Gets the cache type
	 * @return
	 */
	public function getCache() {
	    return $this->getWidgetProperty(self::PROPERTY_CACHE, self::CACHE_AUTO);
	}

	/**
	 * Checks if the cache is set to auto
	 * @return boolean True if the widget decides the cache, false otherwise
	 */
	public function isAutoCache() {
	    return $this->getCache() == self::CACHE_AUTO;
	}

	/**
	 * Checks if the cache is disabled
	 * @return boolean True if the cache is disabled, false otherwise
	 */
	public function isCacheDisabled() {
	    return $this->getCache() == self::CACHE_DISABLED;
	}

	/**
	 * Checks if the cache is enabled
	 * @return boolean True if the cache is enabled, false otherwise
	 */
	public function isCacheEnabled() {
	    return $this->getCache() == self::CACHE_ENABLED;
	}

	/**
	 * Sets the cache time to live
	 * @param integer $ttl Time to live in seconds, 0 for infinit
	 * @return null
	 */
	public function setCacheTtl($ttl = null) {
	    $this->setWidgetProperty(self::PROPERTY_CACHE_TTL, $ttl);
	}

	/**
	 * Gets the cache time to live
	 * @return integer
	 */
	public function getCacheTtl() {
	    return $this->getWidgetProperty(self::PROPERTY_CACHE_TTL, 0);
	}

    /**
     * Checks whether this widget is published
     * @return boolean True if this widget is published, false if not
     */
    public function isPublished() {
        $publish = $this->getWidgetProperty(Node::PROPERTY_PUBLISH, true);
        if (!Boolean::getBoolean($publish)) {
            return false;
        }

        $publishStart = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_START);
        $publishStop = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_STOP);

        if (!$publishStart && !$publishStop) {
            return true;
        }

        $now = time();
        if ($publishStart) {
            $publishStart = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStart);
            $publishStart = $publishStart->getTimestamp();
        }
        if ($publishStop) {
            $publishStop = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStop);
            $publishStop = $publishStop->getTimestamp();
        }

        if ($publishStart && $publishStop) {
            if ($publishStart <= $now && $now < $publishStop) {
                return true;
            }
        } elseif ($publishStart && $publishStart <= $now) {
            return true;
        } elseif ($publishStop && $now < $publishStop) {
            return true;
        }

        return false;
    }

    /**
     * Gets the next time the published state changes
     * @return integer|null A timestamp of the change or null when no change is
     * coming
     */
    public function getDateExpires() {
        $publish = $this->getWidgetProperty(Node::PROPERTY_PUBLISH, true);
        if (!Boolean::getBoolean($publish)) {
            return null;
        }

        $publishStart = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_START);
        $publishStop = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_STOP);

        if (!$publishStart && !$publishStop) {
            return null;
        }

        $now = time();

        if ($publishStart) {
            $publishStart = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStart);
            $publishStart = $publishStart->getTimestamp();

            if ($now < $publishStart) {
                return $publishStart;
            }
        }

        if ($publishStop) {
            $publishStop = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStop);
            $publishStop = $publishStop->getTimestamp();

            if ($now < $publishStop) {
                return $publishStop;
            }
        }

        return null;
    }

    /**
     * Sets the available locales of the widget
     * @param string|array $locales Array with the code of the locale as value
     * @return null
     */
    public function setAvailableLocales($locales) {
        if (is_array($locales)) {
            if (!$locales || in_array(Node::LOCALES_ALL, $locales)) {
                $locales = Node::LOCALES_ALL;
            } else {
                $locales = implode(NodeProperty::LIST_SEPARATOR, $locales);
            }
        } elseif (!$locales) {
            $locales = null;
        }

        $this->setWidgetProperty(Node::PROPERTY_LOCALES, $locales);
    }

    /**
     * Gets the available locales of the widget
     * @return array|string Array with the code of the locales as key and
     * value if a subset of the locales is available, the LOCALES_ALL constant
     * if the widget is available in all locales or if the property is not set.
     */
    public function getAvailableLocales() {
        $availableLocales = $this->getWidgetProperty(Node::PROPERTY_LOCALES);

        if (!$availableLocales || $availableLocales == Node::LOCALES_ALL) {
            return Node::LOCALES_ALL;
        }

        $locales = explode(NodeProperty::LIST_SEPARATOR, $availableLocales);

        $availableLocales = array();
        foreach ($locales as $locale) {
            $locale = trim($locale);

            $availableLocales[$locale] = $locale;
        }

        return $availableLocales;
    }

    /**
     * Gets whether the widget is available in the provided locale
     * @param string $locale Code of the locale
     * @return boolean True if the widget is available in the provided locale, false otherwise
     */
    public function isAvailableInLocale($locale) {
        $locales = $this->getAvailableLocales();
        if ($locales === null || $locales === Node::LOCALES_ALL || isset($locales[$locale])) {
            return true;
        }

        return false;
    }

    /**
     * Gets whether this widget is secured
     * @return boolean|string False when no security, an authentication constant
     * or a comma separated list of permissions otherwise
     */
    public function getSecurity() {
        $security = $this->getWidgetProperty(Node::PROPERTY_SECURITY, Node::AUTHENTICATION_STATUS_EVERYBODY);
        if (!$security || $security === Node::AUTHENTICATION_STATUS_EVERYBODY) {
            return false;
        }

        return $security;
    }

    /**
     * Gets whether the provided user is allowed to view the widget
     * @param ride\library\security\SecurityManager $securityManager
     * @return boolean True if allowed, false otherwise
     */
    public function isAllowed(SecurityManager $securityManager) {
        $security = $this->getSecurity();
        if (!$security) {
            return true;
        }

        try {
            $user = $securityManager->getUser();
        } catch (AuthenticationException $exception) {
            $user = null;
        }

        if ($security === Node::AUTHENTICATION_STATUS_ANONYMOUS) {
            if ($user) {
                return false;
            } else {
                return true;
            }
        }

        if (!$user) {
            return false;
        }

        if ($security === Node::AUTHENTICATION_STATUS_AUTHENTICATED) {
            return true;
        }

        $isAllowed = true;

        $permissions = explode(',', $security);
        foreach ($permissions as $permission) {
            if (!$securityManager->isPermissionGranted($permission)) {
                $isAllowed = false;

                break;
            }
        }

        return $isAllowed;
    }

}
