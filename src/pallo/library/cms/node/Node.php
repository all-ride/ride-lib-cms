<?php

namespace pallo\library\cms\node;

use pallo\library\cms\exception\CmsException;
use pallo\library\cms\widget\NodeWidgetProperties;
use pallo\library\reflection\Boolean;

use \DateTime;

/**
 * Main node object
 */
class Node {

    /**
     * Permission constant to allow everybody to a node
     * @var string
     */
    const AUTHENTICATION_STATUS_EVERYBODY = 'everybody';

    /**
     * Permission constant to allow only anonymous users to a node
     * @var string
     */
    const AUTHENTICATION_STATUS_ANONYMOUS = 'anonymous';

    /**
     * Permission constant to allow only authenticated users to a node
     * @var string
     */
    const AUTHENTICATION_STATUS_AUTHENTICATED = 'authenticated';

    /**
     * Locales value for all locales
     * @var string
     */
    const LOCALES_ALL = 'all';

    /**
     * Separator for the node path
     * @var string
     */
    const PATH_SEPARATOR = '-';

    /**
     * Property key for the locales
     * @var string
     */
    const PROPERTY_LOCALES = 'locales';

    /**
     * Property key for a meta
     * @var string
     */
    const PROPERTY_META = 'meta';

    /**
     * Property key for the name
     * @var string
     */
    const PROPERTY_NAME = 'name';

    /**
     * Property key for the permissions
     * @var string
     */
    const PROPERTY_PERMISSIONS = 'permissions';

    /**
     * Property key for the publish flag
     * @var string
     */
    const PROPERTY_PUBLISH = 'publish';

    /**
     * Property key for the publish start date
     * @var string
     */
    const PROPERTY_PUBLISH_START = 'publish.start';

    /**
     * Property key for the publish stop date
     * @var string
     */
    const PROPERTY_PUBLISH_STOP = 'publish.stop';

    /**
     * Property key for the route
     * @var unknown_type
     */
    const PROPERTY_ROUTE = 'route';

    /**
     * Property key for the theme
     * @var string
     */
    const PROPERTY_THEME = 'theme';

    /**
     * Base setting key for widget properties
     * @var string
     */
    const PROPERTY_WIDGET = 'widget';

    /**
     * Property key for the widgets
     * @var string
     */
    const PROPERTY_WIDGETS = 'widgets';

    /**
     * Type of the node
     * @var string
     */
    protected $type;

	/**
	 * Materialized path of the parent node
	 * @var string
	 */
	protected $parentPath;

	/**
	 * Node of the parent
	 * @var Node
	 */
	protected $parentNode;

    /**
     * Id of the node
     * @var string
     */
	protected $id;

	/**
	 * Order index within the parent
	 * @var integer
	 */
	protected $orderIndex;

	/**
	 * The properties of this node
	 * @var array|NodeProperties
	 */
	protected $properties;

	/**
	 * Array to load the children
	 * @var array
	 */
	protected $children;

	/**
	 * Flag for the default inherit value
	 * @var boolean
	 */
	protected $defaultInherit;

	/**
	 * Widget id for a search result
	 * @var integer
	 */
	private $widgetId;

	/**
	 * Constructs a new node
	 * @param string $type Type of the node
	 * @return null
	 */
	public function __construct($type) {
	    $this->type = $type;
	    $this->id = false;

	    $this->parentPath = '';
	    $this->parentNode = null;
	    $this->orderIndex = null;

        $this->properties = array();
        $this->defaultInherit = false;

        $this->widgetId;
	}

	/**
	 * Get a string representation of the node
	 * @return string
	 */
	public function __toString() {
	    return '[' . $this->type . '::' . $this->getPath() . ']';
	}

	/**
	 * Gets the type of this node
	 * @return string
	 */
	public function getType() {
	    return $this->type;
	}

	/**
	 * Sets the id of this node
	 * @param string $id
	 * @return null
	 */
	public function setId($id) {
	    $this->id = $id;
	}

	/**
	 * Gets the id of this node
	 * @return string
	 */
	public function getId() {
	    return $this->id;
	}

	/**
	 * Sets the materialized path of the parent node
	 * @param string $parent The materialized path of the parent node
	 * @param integer $order Index of this node in the list of the children
	 * @return null
	 */
	public function setParent($parent) {
	    $this->parentPath = $parent;
	}

	/**
	 * Gets the materialized path of the parent node
	 * @return string
	 */
	public function getParent() {
	    return $this->parentPath;
	}

	/**
	 * Sets the order index of this node in the list of the parent's children
	 * @param integer $orderIndex
	 * @return null
	 */
	public function setOrderIndex($orderIndex) {
	    $this->orderIndex = $orderIndex;
	}

	/**
	 * Gets the order index of this node in the list of the parent's children
	 * @return integer
	 */
	public function getOrderIndex() {
	    return $this->orderIndex;
	}

    /**
     * Get the materialized path of the node. The path is used for the parent
     * field of a node.
     * @return string
     */
    public function getPath() {
        if (!$this->parentPath) {
        	return $this->id;
        }

        return $this->parentPath . self::PATH_SEPARATOR . $this->id;
    }

    /**
     * Get the id of the root node
     * @return string
     */
    public function getRootNodeId() {
        if (!$this->id && !$this->parentPath) {
            throw new CmsException('Could not get root node id: this is a new node so it has no root node');
        }

        if (!$this->parentPath) {
        	return $this->id;
        }

        $tokens = explode(self::PATH_SEPARATOR, $this->parentPath);

        return array_shift($tokens);
    }

    /**
     * Get the id of the parent node
     * @return integer
     */
    public function getParentNodeId() {
    	if (!$this->parentPath) {
            if (!$this->id) {
                throw new CmsException('Could not get the parent node: this is a new node so it has no parent node');
            }

    		return null;
    	}

        $ids = explode(self::PATH_SEPARATOR, $this->parentPath);

        return array_pop($ids);
    }

    /**
     * Checks if the provided node is a parent node of this node
     * @param integer $nodeId The id of the node to check as a parent
     * @return boolean True if the provided node is a parent, false otherwise
     */
    public function hasParent($nodeId = null) {
        if (!$nodeId) {
            return $this->parentPath ? true : false;
        }

        if (!$this->parentPath) {
            return false;
        }

    	$ids = explode(self::PATH_SEPARATOR, $this->parentPath);

    	return in_array($nodeId, $ids);
    }

    /**
     * Gets the level of this node in the hierarchy
     * @return integer
     */
    public function getLevel() {
    	if (!$this->parentPath) {
    		return 0;
    	}

    	return substr_count($this->parentPath, self::PATH_SEPARATOR) + 1;
    }

    /**
     * Sets the parent node
     * @param Node|null Parent node if set, null otherwise
     */
    public function setParentNode(Node $node = null) {
        $this->parentNode = $node;
        $this->parentPath = $node->getPath();
    }

    /**
     * Gets the parent node
     * @return Node|null Parent node if set, null otherwise
     */
    public function getParentNode() {
        return $this->parentNode;
    }

    /**
     * Gets the root node
     * @return Node|null Root node if the parent node is set, null otherwise
     */
    public function getRootNode() {
        if ($this->id == $this->getRootNodeId()) {
            return $this;
        }

        $rootNode = null;

        $parentNode = $this->parentNode;
        while ($parentNode) {
            $rootNode = $parentNode;
            $parentNode = $parentNode->getParentNode();
        }

        return $rootNode;
    }

    /**
     * Sets the children to this node
     * @param array $children Array with the node id as key and the node as
     * value
     * @return null
     */
    public function setChildren(array $children) {
        $this->children = $children;
    }

    /**
     * Gets the children of this node
     * @return array|null An array with the children if loaded, null otherwise
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Sets the default inherit value
     * @param boolean $defaultInherit
     * @return null
     */
    public function setDefaultInherit($defaultInherit) {
        $this->defaultInherit = $defaultInherit;
    }

    /**
     * Sets a property
     * @param NodeProperty $property
     * @return null
     */
    public function setProperty(NodeProperty $property) {
        $this->properties[$property->getKey()] = $property;
    }

    /**
     * Gets a property
     * @param string $key Key of the property
     * @return NodeProperty|null
     */
    public function getProperty($key) {
        $this->checkPropertyKey($key);

        if (!isset($this->properties[$key])) {
            return null;
        }

        return $this->properties[$key];
    }

    /**
     * Sets a node property
     * @param string $key Key for the property
     * @param string $value Value of the property, null to unset the property
     * @param boolean|null $inherit True to inherit this setting to lower
     * levels, false to not inherit and null to use the previous inherit state
     * @return null
     * @throws pallo\library\cms\exception\CmsException when the key is invalid
     */
    public function set($key, $value, $inherit = null) {
        $this->checkPropertyKey($key);

        if ($inherit === null) {
            // detect the inherit value
            $inheritPrefixLength = strlen(NodeProperty::INHERIT_PREFIX);
            if (strlen($key) > $inheritPrefixLength && strncmp($key, NodeProperty::INHERIT_PREFIX, $inheritPrefixLength) == 0) {
                $key = substr($key, $inheritPrefixLength);

                $defaultInherit = true;
            } else {
                $defaultInherit = $this->defaultInherit;
            }
        } else {
            $defaultInherit = $inherit;
        }

        if (($value === null || $value === '')) {
            if (isset($this->properties[$key])) {
                // value is set, unset it
                unset($this->properties[$key]);
            } elseif ($this->parentNode->get($key, null, true, true) !== null) {
                // value is set on parent node, override it with empty value
                $this->setProperty(new NodeProperty($key, $value, $defaultInherit));
            }

            return;
        } elseif (isset($this->properties[$key])) {
            // key exists, update value and inheritance
            $this->properties[$key]->setValue($value);

            if ($inherit) {
                $this->properties[$key]->setInherit($inherit);
            }
        } else {
            // new property
            $this->setProperty(new NodeProperty($key, $value, $defaultInherit));
        }
    }

    /**
     * Gets a value of a node property
     * @param string $key Key of the property
     * @param mixed $default Default value for when the property is not set
     * @param boolean $inherited True to look in inherited properties, false to
     * only look in this node
     * @param boolean $inheritedPropertyRequired True to only return the value
     * if the property will inherit, needed internally for recursive lookup
     * @return mixed Value of the property if found, the provided default value
     * otherwise
     * @throws pallo\library\cms\exception\CmsException when the key is invalid
     */
    public function get($key, $default = null, $inherited = true, $inheritedPropertyRequired = false) {
        $this->checkPropertyKey($key);

        if (isset($this->properties[$key]) && (!$inheritedPropertyRequired || ($inheritedPropertyRequired && $this->properties[$key]->getInherit()))) {
            return $this->properties[$key]->getValue();
        }

        if ($inherited && $this->parentNode) {
            return $this->parentNode->get($key, $default, true, true);
        }

        return $default;
    }

    /**
     * Checks whether a key is a non empty string
     * @param mixed $key Key for a property
     * @return null
     * @throws pallo\library\cms\exception\CmsException when the key is invalid
     */
    protected function checkPropertyKey($key) {
        if (!is_string($key) || $key === '') {
            throw new CmsException('Provided key is empty or invalid');
        }
    }

    /**
     * Sets the properties of this node
     * @param array $properties Array with the key of the property as key and
     * an instance of NodeProperty as value
     */
    public function setProperties(array $properties) {
        $this->properties = $properties;
    }

    /**
     * Gets the properties of this node
     * @return array Array with the property key as key and a NodeProperty
     * instance as value
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * Set the name of this node for the provided locale
     * @param string $locale
     * @param string $name
     * @return null
     */
    public function setName($locale, $name) {
        $this->set(self::PROPERTY_NAME . '.' . $locale, $name, false);
    }

    /**
     * Gets the name of this node for the provided locale
     * @param string $locale Code of the locale
     * @return string The name of this node
     */
    public function getName($locale = null) {
        if ($locale) {
            $name = $this->get(self::PROPERTY_NAME . '.' . $locale);
            if ($name) {
                return $name;
            }
        }

        foreach ($this->properties as $key => $value) {
            if ($key == self::PROPERTY_NAME || strpos($key, self::PROPERTY_NAME . '.') === 0) {
                return $value->getValue();
            }
        }

        return null;
    }

    /**
     * Sets the route of this node for the provided locale
     * @param string $locale The code of the locale
     * @param string $route The route of this node
     */
    public function setRoute($locale, $route) {
        $this->set(self::PROPERTY_ROUTE . '.' . $locale, $route, false);
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
        $route = $this->get(self::PROPERTY_ROUTE . '.' . $locale);

        if (!$route && $this->id && $returnDefault) {
            $route = '/nodes/' . $this->id . '/' . $locale;
        }

        return $route;
    }

    /**
     * Sets the name of the theme
     * @param string $theme
     * @return null
     */
    public function setTheme($theme) {
        $this->set(self::PROPERTY_THEME, $theme);
    }

    /**
     * Gets the name of the theme
     * @return string|null
     */
    public function getTheme() {
        return $this->get(self::PROPERTY_THEME);
    }

    /**
     * Check whether this node is published
     * @return boolean True if this node is published, false if not
     */
    public function isPublished() {
        $publish = $this->get(self::PROPERTY_PUBLISH, false);
        if (!Boolean::getBoolean($publish)) {
            return false;
        }

        $now = time();
        $publishStart = $this->get(self::PROPERTY_PUBLISH_START);
        $publishStart = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $publishStart);
        $publishStop = $this->get(self::PROPERTY_PUBLISH_STOP);
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

    /**
     * Sets the available locales to this node
     * @param string|array $locales Array with the code of the locale as value
     * @return null
     */
    public function setAvailableLocales($locales) {
        if (is_array($locales)) {
            if (!$locales || in_array(self::LOCALES_ALL, $locales)) {
                $locales = self::LOCALES_ALL;
            } else {
                $locales = implode(NodeProperty::LIST_SEPARATOR, $locales);
            }
        }

        $this->set(self::PROPERTY_LOCALES, $locales);
    }

    /**
     * Gets the available locales of this node
     * @return array|string Array with the code of the locales as key and
     * value if a subset of the locales is available, the LOCALES_ALL constant
     * if the node is available in all locales or if the property is not set.
     */
    public function getAvailableLocales() {
        $availableLocales = $this->get(self::PROPERTY_LOCALES);

        if (!$availableLocales || $availableLocales == self::LOCALES_ALL) {
            return self::LOCALES_ALL;
        }

        $locales = explode(self::LIST_SEPARATOR, $availableLocales);

        $availableLocales = array();
        foreach ($locales as $locale) {
            $locale = trim($locale);

            $availableLocales[$locale] = $locale;
        }

        return $availableLocales;
    }

    /**
     * Gets whether this node is available in the provided locale
     * @param string $locale Code of the locale
     * @return boolean True if the node is available in the provided locale, false otherwise
     */
    public function isAvailableInLocale($locale) {
        $locales = $this->getAvailableLocales();
        if ($locales === null || $locales === self::LOCALES_ALL || isset($locales[$locale])) {
            return true;
        }

        return false;
    }

    /**
     * Sets a meta field
     * @param string $locale Code of the locale
     * @param string $name Name of the meta
     * @param string $value Value for the meta
     * @return null
     */
    public function setMeta($locale, $name, $value) {
        $this->set(self::PROPERTY_META . '.' . $locale . '.' . $name, $value);
    }

    /**
     * Gets a meta field
     * @param string $locale Code of the locale
     * @param string $name Name of the meta
     * @return string Value for the meta
     */
    public function getMeta($locale, $name) {
        return $this->get(self::PROPERTY_META . '.' . $locale . '.' . $name);
    }

    /**
     * Gets a widget properties for the provided widget
     * @param integer $widgetId Id of the widget
     * @return joppa\model\widget\WidgetProperties
     */
    public function getWidgetProperties($widgetId) {
        return new NodeWidgetProperties($this, $widgetId);
    }

	/**
	 * Gets the id of a widget
	 * @param integer $widgetId Id of the widget instance
	 * @return string Id of the widget
	 */
	public function getWidget($widgetId) {
	    $widget = $this->get(self::PROPERTY_WIDGET . '.' . $widgetId);
	    if (!$widget) {
            throw new CmsException('Could not get widget ' . $widgetId . ': widget not found');
        }

        return $widget;
	}

	/**
     * Get the widgets for a region
     * @param string $region name of the region
     * @return array Array with the widget instance id as key and the widget id
     * as value
	 */
    public function getWidgets($region) {
        $widgets = array();

        $widgetString = $this->get(self::PROPERTY_WIDGETS . '.' . $region);
        if (!$widgetString) {
        	return $widgets;
        }

        $widgetIds = explode(NodeProperty::LIST_SEPARATOR, $widgetString);
        foreach ($widgetIds as $widgetId) {
            $widgetId = trim($widgetId);

            $widgets[$widgetId] = $this->get(self::PROPERTY_WIDGET . '.' . $widgetId);
        }

        return $widgets;
    }

	/**
     * Get the inherited widgets for a region
     * @param string $region Name of the region
     * @return array Array with the widget id as key and value
	 */
    public function getInheritedWidgets($region) {
        $inheritedWidgets = array();

        if (!$this->hasParent()) {
            return $inheritedWidgets;
        }

        $parent = $this->getParentNode();

        $widgetString = $parent->get(self::PROPERTY_WIDGETS . '.' . $region, null, true, true);
        if (!$widgetString) {
        	return $inheritedWidgets;
        }

        $widgetIds = explode(NodeProperty::LIST_SEPARATOR, $widgetString);
        foreach ($widgetIds as $widgetId) {
            $widgetId = trim($widgetId);

            $inheritedWidgets[$widgetId] = $widgetId;
        }

        return $inheritedWidgets;
    }

    /**
     * Adds a widget to a region
     * @param string $region Name of the region
     * @param integer $widgetId Id of the new widget instance
     * @return null
     */
    public function addWidget($region, $widgetId) {
        $key = self::PROPERTY_WIDGETS . '.' . $region;

        $nodeWidgets = $this->get($key);
        if (!$nodeWidgets) {
            $nodeWidgets = $widgetId;
        } else {
            $nodeWidgets .= NodeProperty::LIST_SEPARATOR . $widgetId;
        }

        $this->set($key, $nodeWidgets);
    }

    /**
     * Deletes a widget from a region
     * @param string $region Name of the region
     * @param int $id Id of the widget instance
     * @return null
     * @throws pallo\library\cms\exception\CmsException when a widget could not
     * be found
     */
    public function deleteWidget($region, $widgetId) {
        $widgetsKey = self::PROPERTY_WIDGETS . '.' . $region;

        $widgetsValue = $this->get($widgetsKey);
        if (!$widgetsValue) {
            return;
        }

        $widgetIds = explode(NodeProperty::LIST_SEPARATOR, $widgetsValue);
        $widgetsValue = '';

        $foundWidget = false;
        foreach ($widgetIds as $id) {
            if ($id == $widgetId) {
            	$foundWidget = true;

                continue;
            }

            $widgetsValue .= ($widgetsValue ? NodeProperty::LIST_SEPARATOR : '') . $id;
        }

        if (!$foundWidget) {
        	throw new CmsException('Could not delete widget with id ' . $widgetId . ': widget not found');
        }

        // remove properties of the widget
        $properties = $this->getWidgetProperties($id);
        $properties->clearWidgetProperties();

        // remove the widget
        $this->set($widgetsKey, $widgetsValue);
    }

    /**
     * Order the widgets of a region
     * @param string $region Name of the region
     * @param string|array $widgets Array with widget ids or a string with
     * widget ids separated by a comma.
     * @return null
     * @throws pallo\library\cms\exception\CmsException when the widgets could
     * not be ordered
     */
    public function orderWidgets($region, $widgets) {
        if (!is_array($widgets)) {
            $widgets = explode(NodeProperty::LIST_SEPARATOR, $widgets);
        }

        $widgetsKey = self::PROPERTY_WIDGETS . '.' . $region;
        $currentWidgets = explode(NodeProperty::LIST_SEPARATOR, $this->get($widgetsKey, ''));

        $widgetsValue = '';
        foreach ($widgets as $widgetId) {
            $widgetId = trim($widgetId);

            $key = array_search($widgetId, $currentWidgets);
            if ($key === false) {
                throw new CmsException('Could not order widgets: widget ' . $widgetId . ' is not set to region ' . $region);
            }

            $widgetsValue .= ($widgetsValue ? NodeProperty::LIST_SEPARATOR : '') . $widgetId;

            unset($currentWidgets[$key]);
        }

        $numCurrentWidgets = count($currentWidgets);
        if ($numCurrentWidgets) {
            $widget = array_pop($currentWidgets);
            if ($numCurrentWidgets > 1) {
                throw new CmsException('Could not order widgets: widgets ' . implode(NodeProperty::LIST_SEPARATOR, $currentWidgets) . ' and ' . $widget . ' are not found in the new widget order');
            }

            throw new CmsException('Could not order widgets: widget ' . $widget . ' is not found in the new widget order');
        }

        $this->set($widgetsKey, $widgetsValue);
    }

    /**
     * Sets the widget instance id of the queried widget
     * @return integer
     * @see NodeModel::getNodesForWidget
     */
    public function setWidgetId($widgetId) {
        $this->widgetId = $widgetId;
    }

    /**
     * Gets the widget id of the queried widget
     * @return integer
     * @see NodeModel::getNodesForWidget
     */
    public function getWidgetId() {
        return $this->widgetId;
    }

}