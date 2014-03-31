<?php

namespace ride\library\cms\widget;

use ride\library\cms\exception\CmsException;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeProperty;
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
     * @return ride\library\cms\node\Node
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
	 * Clear the settings of this widget
	 * @return null
	 */
	public function clearWidgetProperties() {
	    $properties = $this->node->getProperties();

	    foreach ($properties as $key => $property) {
	        if (strpos($key, $this->widgetPropertyPrefix) === 0) {
	            unset($properties[$key]);
	        }
	    }

	    $this->node->setProperties($properties);
	}

	/**
	 * Sets the cache type
	 * @param string $type
	 * @return null
	 * @throws ride\library\cms\exception\CmsException when an invalid cache
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

        $now = time();
        $publishStart = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_START);
        $publishStart = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStart);
        $publishStop = $this->getWidgetProperty(Node::PROPERTY_PUBLISH_STOP);
        $publishStop = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStop);

        if ($publishStart && $publishStop) {
            if ($publishStart->getTimestamp() <= $now && $now < $publishStop->getTimestamp()) {
                return true;
            }
        } elseif ($publishStart) {
            if ($publishStart->getTimestamp() <= $now) {
                return true;
            }
        } elseif ($publishStop) {
            if ($now < $publishStop->getTimestamp()) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

}
