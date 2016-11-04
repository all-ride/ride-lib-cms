<?php

namespace ride\library\cms\node;

use ride\library\cms\exception\CmsException;
use ride\library\cms\widget\NodeWidgetProperties;
use ride\library\reflection\Boolean;
use ride\library\security\exception\AuthenticationException;
use ride\library\security\SecurityManager;

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
     * Delimiter to open a block
     * @var string
     */
    const BLOCK_OPEN = '[';

    /**
     * Delimiter to close a block
     * @var string
     */
    const BLOCK_CLOSE = ']';

    /**
     * Property key for the layout
     * @var string
     */
    const PROPERTY_LAYOUT = 'layout';

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
     * Property key for a header
     * @var string
     */
    const PROPERTY_HEADER = 'header';

    /**
     * Property key for the name
     * @var string
     */
    const PROPERTY_NAME = 'name';

    /**
     * Property key for the description
     * @var string
     */
    const PROPERTY_DESCRIPTION = 'description';

    /**
     * Property key for the full width flag
     * @var string
     */
    const PROPERTY_FULL_WIDTH = 'full.width';

    /**
     * Property key for the image
     * @var string
     */
    const PROPERTY_IMAGE = 'image';

    /**
     * Property key for the hide in menu flag
     * @var string
     */
    const PROPERTY_HIDE_MENU = 'hide.menu';

    /**
     * Property key for the hide in breadcrumbs flag
     * @var string
     */
    const PROPERTY_HIDE_BREADCRUMBS = 'hide.breadcrumbs';

    /**
     * Property key for the hide for anonymous users flag
     * @var string
     */
    const PROPERTY_HIDE_ANONYMOUS = 'hide.anonymous';

    /**
     * Property key for the hide for authenticated users flag
     * @var string
     */
    const PROPERTY_HIDE_AUTHENTICATED = 'hide.authenticated';

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
     * Property key for region properties
     * @var string
     */
    const PROPERTY_REGION = 'region';

    /**
     * Property key for the route
     * @var unknown_type
     */
    const PROPERTY_ROUTE = 'route';

    /**
     * Property key for the sections of a region
     * @var string
     */
    const PROPERTY_SECTIONS = 'sections';

    /**
     * Property key for the security flag
     * @var string
     */
    const PROPERTY_SECURITY = 'security';

    /**
     * Property key for the theme
     * @var string
     */
    const PROPERTY_THEME = 'theme';

    /**
     * Property key for the title
     * @var string
     */
    const PROPERTY_TITLE = 'title';

    /**
     * Property key for style
     * @var string
     */
    const PROPERTY_STYLE = 'style';

    /**
     * Base setting key for widget properties
     * @var string
     */
    const PROPERTY_WIDGET = 'widget';

    /**
     * Suffix setting key for the widgets of a region
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
     * Revision of the node
     * @var string
     */
    protected $revision;

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
     * UNIX timestamp of the last change
     * @var integer
     */
    protected $dateModified;

    /**
     * UNIX timestamp when this node is going to change content
     * @var integer|null
     */
    protected $dateExpires;

    /**
     * array with context variables
     * @var array()
     */
    protected $context;

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
        $this->revision = null;
        $this->dateModified = 0;
        $this->dateExpires = null;

        $this->properties = array();
        $this->defaultInherit = false;

        $this->widgetId;
        $this->context = array();
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
     * Gets a global id for this node
     * @return string
     */
    public function getGlobalId() {
        return $this->getRootNodeId() . self::PATH_SEPARATOR . $this->getId();
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
     * Gets a child by it's route
     * @param string $locale Code of the locale
     * @param string $route Route to find a child for
     * @return Node|null
     */
    public function getChildByRoute($route, &$locale, array $locales) {
        if (!$this->children) {
            return null;
        }

        $result = array();

        foreach ($this->children as $child) {
            // check custom route and default routes
            foreach ($locales as $l) {
                $childRoute = $child->getRoute($l);
                if (strpos($route, $childRoute) === 0) {
                    $result[] = array(
                        'node' => $child,
                        'locale' => $l,
                        'length' => strlen($childRoute),
                    );
                    $locale = $l;
                }
            }

            // check children
            $child = $child->getChildByRoute($route, $locale, $locales);
            if ($child) {
                return $child;
            }
        }

        $node = null;
        $routeLength = -1;

        foreach ($result as $nodeArray) {
            if ($routeLength == -1 || $nodeArray['length'] > $routeLength) {
                $node = $nodeArray['node'];
                $locale = $nodeArray['locale'];
                $routeLength = $nodeArray['length'];
            }
        }

        return $node;
    }

    /**
     * Sets the revision of this node
     * @param string $revision Revision of this node
     * @return null
     */
    public function setRevision($revision) {
        $this->revision = $revision;
    }

    /**
     * Gets the revision of this node
     * @return string
     */
    public function getRevision() {
        return $this->revision;
    }

    /**
     * Sets the date of the last modification
     * @param integer $dateModified UNIX timestamp of last modification
     * @return null
     */
    public function setDateModified($dateModified) {
        $this->dateModified = $dateModified;
    }

    /**
     * Gets the date of the last modification
     * @return integer UNIX timestamp of last modification
     */
    public function getDateModified() {
        return $this->dateModified;
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
     * @throws \ride\library\cms\exception\CmsException when the key is invalid
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

        if ($value === null) {
            if (isset($this->properties[$key])) {
                // value is set, unset it
                unset($this->properties[$key]);
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
     * @throws \ride\library\cms\exception\CmsException when the key is invalid
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
     * Gets a localized property
     * @param string $locale Code of the locale
     * @param string $key Key of the property
     * @param mixed $default default value for when the property is not set
      * @return mixed Property value or $default if the property was not set
     */
    public function getLocalized($locale, $key, $default = null) {
        $this->checkPropertyKey($key);

        $properties = $this->getProperties($key, true);

        if (isset($properties[$key . '.' . $locale])) {
            return $properties[$key . '.' . $locale]->getValue();
        } elseif (isset($properties[$key])) {
            return $properties[$key]->getValue();
        }

        return $default;
    }

    /**
     * Sets a localized property
     * @param string $locale Code of the locale
     * @param string $key Key of the property
     * @param mixed $value Value to set
      * @return
     */
    public function setLocalized($locale, $key, $value) {
        $properties = $this->getProperties($key, true);
        if (isset($properties[$key . '.' . $locale])) {
            if ($properties[$key . '.' . $locale]->getValue() === $value) {
                return;
            }
        } elseif (isset($properties[$key])) {
            if ($properties[$key]->getValue() === $value) {
                return;
            }
        }

        if (!$properties) {
            $this->set($key, $value);
        } else {
            $this->set($key . '.' . $locale, $value);
        }
    }

    /**
     * Gets a contextualized property
     * @param string $key Key of the property
     * @param string $context Code of the context
     * @param string $locale Code of the locale
     * @param mixed $default default value for when the property is not set
      * @return mixed Property value or $default if the property was not set
     */
    protected function getContextualized($key, $context = null, $locale = null, $default = null) {
        if ($context) {
            $context = '.' . $context;
        }

        if ($locale) {
            // context value for the provided locale
            $value = $this->get($key . '.' . $locale . $context);
            if ($value) {
                return $value;
            }

            if ($context) {
                // general value for the provided locale
                $value = $this->get($key . '.' . $locale);
                if ($value) {
                    return $value;
                }
            }
        }

        // context value for any locale
        foreach ($this->properties as $k => $p) {
            if ($k == $key . $context || (strpos($k, $key . '.') === 0 && (!$context || strpos($k, $context)))) {
                return $p->getValue();
            }
        }

        if ($context) {
            // general value for any locale
            return $this->getContextualized($key);
        }

        // no value
        return $default;
    }

    /**
     * Checks whether a key is a non empty string
     * @param mixed $key Key for a property
     * @return null
     * @throws \ride\library\cms\exception\CmsException when the key is invalid
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
     * @param string $prefix Prefix of the properties to fetch
     * @param boolean $inherited True to look in inherited properties, false to
     * only look in this node
     * @param boolean $inheritedPropertyRequired True to only return the value
     * if the property will inherit, needed internally for recursive lookup
     * @return array Array with the property key as key and a NodeProperty
     * instance as value
     */
    public function getProperties($prefix = null, $inherited = false, $inheritedPropertyRequired = false) {
        $properties = array();
        foreach ($this->properties as $key => $property) {
            if (($prefix && strpos($key, $prefix) !== 0) || ($inheritedPropertyRequired && !$property->getInherit())) {
                continue;
            }

            $properties[$key] = $property;
        }

        if ($inherited && $this->parentNode) {
            $properties += $this->parentNode->getProperties($prefix, true, true);
        }

        return $properties;
    }

    /**
     * Set the name of this node for the provided locale
     * @param string $locale Code of the locale
     * @param string $name Name of the node in the provided locale
     * @param string $context Name of the context (menu, breadcrumb, title, ...)
     * @return null
     */
    public function setName($locale, $name, $context = null) {
        if ($context) {
            $context = '.' . $context;
        }

        $this->set(self::PROPERTY_NAME . '.' . $locale . $context, $name, false);
    }

    /**
     * Gets the name of this node for the provided locale
     * @param string $locale Code of the locale
     * @param string $context Name of the context (menu, breadcrumb, title, ...)
     * @return string The name of this node
     */
    public function getName($locale = null, $context = null) {
        return $this->getContextualized(self::PROPERTY_NAME, $context, $locale);
    }

    /**
     * Sets the description of this node
     * @param string $locale Code of the locale
     * @param string $description Description of this node
     * @return null
     */
    public function setDescription($locale, $description, $context = null) {
        if ($context) {
            $context = '.' . $context;
        }

        $this->set(self::PROPERTY_DESCRIPTION . '.' . $locale . $context, $description, false);
    }

    /**
     * Gets the description of this node
     * @param string $locale Code of the locale
     * @return string|null Description of this node if set, null otherwise
     */
    public function getDescription($locale, $context = null) {
        return $this->getContextualized(self::PROPERTY_DESCRIPTION, $context, $locale);
    }

    /**
     * Sets the image of this node
     * @param string $locale Code of the locale
     * @param string $image Path to the image
     * @return null
     */
    public function setImage($locale, $image) {
        $this->setLocalized($locale, self::PROPERTY_IMAGE, $image);
    }

    /**
     * Gets the image of this node
     * @param string $locale Code of the locale
     * @return string|null Path to the image if set, null otherwise
     */
    public function getImage($locale) {
        return $this->getLocalized($locale, self::PROPERTY_IMAGE);
    }

    /**
     * Checks if this node is the home page
     * @param string $locale Code of the locale
     * @return boolean
     */
    public function isHomepage($locale) {
        return $this->getRoute($locale) === '/';
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
     * Gets the set routes of this node
     * @return array Array with the locale code as key and the route as value
     */
    public function getRoutes() {
        $routes = array();

        $prefixLength = strlen(self::PROPERTY_ROUTE . '.');

        foreach ($this->properties as $key => $property) {
            if (strpos($key, self::PROPERTY_ROUTE . '.') !== 0) {
                continue;
            }

            $routes[substr($key, $prefixLength)] = $property->getValue();
        }

        return $routes;
    }

    /**
     * Gets the full URL to this node
     * @param string $locale Code of the locale
     * @param string $baseUrl Fallback for when the root node is no site node or
     * when it has no base URL set
     * @return string
     */
    public function getUrl($locale, $baseUrl) {
        $rootNode = $this->getRootNode();
        if ($rootNode instanceof SiteNode) {
            $url = $rootNode->getBaseUrl($locale);
        }

        if (!$url) {
            $url = $baseUrl;
        }

        return $url . $this->getRoute($locale);
    }

    /**
     * Gets all the full URLs of this node
     * @param string $baseUrl Fallback for when the root node is no site node or
     * when it has no base URL set
     * @return array Array with the locale code as key and the URL as value
     */
    public function getUrls($baseUrl) {
        $urls = $this->getRoutes();

        foreach ($urls as $locale => $route) {
            $urls[$locale] = $this->getUrl($locale, $baseUrl);
        }

        return $urls;
    }

    /**
     * Makes an absolute URL for the provided relative URL
     * @param string $locale Code of the current locale
     * @param string $baseUrl Base URL to the system
     * @param string $url Relative URL path or absolute URL to parse
     * @return string Provided URL made absolute
     */
    public function resolveUrl($locale, $baseUrl, $url) {
        if ($url == '' || $url{0} == '#' || strncmp($url, 'mailto:', 7) === 0 || strncmp($url, 'http:', 5) === 0 || strncmp($url, 'https:', 6) === 0 || ($url{0} == '/' && $url{1} == '/')) {
            return $url;
        }

        if (strncmp($url, './', 2) === 0) {
            $baseUrl = $this->getUrl($locale, $baseUrl);
            $baseUrl = rtrim($baseUrl, '/');

            $url = $baseUrl . substr($url, 1);
        } elseif (strncmp($url, '../', 3) === 0) {
            $baseUrl = $this->getUrl($locale, $baseUrl);
            $baseUrl = rtrim($baseUrl, '/');

            do {
                $position = strrpos($baseUrl, '/');
                if ($position === false) {
                    break;
                }

                $baseUrl = substr($baseUrl, 0, $position);
                $url = substr($url, 3);
            } while (strncmp($url, '../', 3) === 0);

            $url = $baseUrl . '/' . $url;
        } else {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        return $url;
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
     * Sets whether to hide this node in the breadcrumbs
     * @param boolean $flag True to hide, false to show
     * @param boolean|null $inherit True to inherit this setting to lower
     * levels, false to not inherit and null to use the previous inherit state
     * @return null
     */
    public function setHideInBreadcrumbs($flag, $inherit = null) {
        $this->set(self::PROPERTY_HIDE_BREADCRUMBS, $flag ? 1 : 0, $inherit);
    }

    /**
     * Gets whether to hide this node in the breadcrumbs
     * @return boolean
     */
    public function hideInBreadcrumbs() {
        return $this->get(self::PROPERTY_HIDE_BREADCRUMBS);
    }

    /**
     * Sets whether to hide this node in the menu
     * @param boolean $flag True to hide, false to show
     * @param boolean|null $inherit True to inherit this setting to lower
     * levels, false to not inherit and null to use the previous inherit state
     * @return null
     */
    public function setHideInMenu($flag, $inherit = null) {
        $this->set(self::PROPERTY_HIDE_MENU, $flag ? 1 : 0, $inherit);
    }

    /**
     * Gets whether to hide this node in the menu
     * @return boolean
     */
    public function hideInMenu() {
        return $this->get(self::PROPERTY_HIDE_MENU);
    }

    /**
     * Sets whether to hide this node for anonymous users
     * @param boolean $flag True to hide, false to show
     * @param boolean|null $inherit True to inherit this setting to lower
     * levels, false to not inherit and null to use the previous inherit state
     * @return null
     */
    public function setHideForAnonymousUsers($flag, $inherit = null) {
        $this->set(self::PROPERTY_HIDE_ANONYMOUS, $flag ? 1 : 0, $inherit);
    }

    /**
     * Gets whether to hide this node for anonymous users
     * @return boolean
     */
    public function hideForAnonymousUsers() {
        return $this->get(self::PROPERTY_HIDE_ANONYMOUS);
    }

    /**
     * Sets whether to hide this node for authenticated users
     * @param boolean $flag True to hide, false to show
     * @param boolean|null $inherit True to inherit this setting to lower
     * levels, false to not inherit and null to use the previous inherit state
     * @return null
     */
    public function setHideForAuthenticatedUsers($flag, $inherit = null) {
        $this->set(self::PROPERTY_HIDE_AUTHENTICATED, $flag ? 1 : 0, $inherit);
    }

    /**
     * Gets whether to hide this node for authenticated users
     * @return boolean
     */
    public function hideForAuthenticatedUsers() {
        return $this->get(self::PROPERTY_HIDE_AUTHENTICATED);
    }

    /**
     * Check whether this node is published
     * @return boolean True if this node is published, false if not
     */
    public function isPublished() {
        $publish = $this->get(self::PROPERTY_PUBLISH, false);
        if (!$publish || !Boolean::getBoolean($publish)) {
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

        $locales = explode(NodeProperty::LIST_SEPARATOR, $availableLocales);

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
     * Gets whether this node is secured
     * @return boolean|string False when no security, an authentication constant
     * or a comma separated list of permissions otherwise
     */
    public function getSecurity() {
        $security = $this->get(self::PROPERTY_SECURITY, self::AUTHENTICATION_STATUS_EVERYBODY);
        if (!$security || $security === self::AUTHENTICATION_STATUS_EVERYBODY) {
            return false;
        }

        return $security;
    }

    /**
     * Gets whether the provided user is allowed to view this node
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

        if ($security === self::AUTHENTICATION_STATUS_ANONYMOUS) {
            if ($user) {
                return false;
            } else {
                return true;
            }
        }

        if (!$user) {
            return false;
        }

        if ($security === self::AUTHENTICATION_STATUS_AUTHENTICATED) {
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

    /**
     * Sets a meta property
     * @param string $locale Code of the locale
     * @param string $name Name of the meta property
     * @param string $value Value for the meta property
     * @return null
     */
    public function setMeta($locale, $name, $value = null) {
        $prefix = self::PROPERTY_META . '.' . $locale . '.';

        if (is_array($name)) {
            // remove all meta
            foreach ($this->properties as $key => $property) {
                if (strpos($key, $prefix) === 0) {
                    unset($this->properties[$key]);
                }
            }

            // set it again
            foreach ($name as $property => $content) {
                $this->set($prefix . $property, $content);
            }
        } else {
            // set a single meta
            $this->set($prefix . $name, $value);
        }
    }

    /**
     * Gets a meta property
     * @param string $locale Code of the locale
     * @param string $name Name of the meta property
     * @param boolean $inherited
     * @return string|array Value of the property when a name has been
     * provided, all the meta properties in an array otherwise
     */
    public function getMeta($locale, $name = null, $inherited = false) {
        $prefix = self::PROPERTY_META . '.' . $locale . '.';

        if ($name) {
            return $this->get($prefix . $name, null, $inherited);
        }

        $prefixLength = strlen($prefix);

        $parentNode = $this->getParentNode();
        if ($inherited && $parentNode) {
            $meta = $parentNode->getMeta($locale, null, true);
        } else {
            $meta = array();
        }

        foreach ($this->properties as $key => $property) {
            if (strpos($key, $prefix) !== 0) {
                continue;
            }

            $meta[str_replace($prefix, '', $key)] = $property->getValue();
        }

        return $meta;
    }

    /**
     * Sets custom response headers
     * @param string $locale Code of the locale
     * @param string|array $header Name of the header or an array of headers
     * @param string $value Value for the header
     * @return null
     */
    public function setHeader($locale, $header, $value = null) {
        $prefix = self::PROPERTY_HEADER . '.' . $locale . '.';

        if (is_array($header)) {
            // remove all headers
            foreach ($this->properties as $key => $property) {
                if (strpos($key, $prefix) === 0) {
                    unset($this->properties[$key]);
                }
            }

            // set it again
            foreach ($header as $name => $value) {
                $this->set($prefix . strtolower($name), $value);
            }
        } else {
            $this->set($prefix . strtolower($header), $value);
        }
    }

    /**
     * Gets the custom headers of this node
     * @param string $locale Code of the locale
     * @param string $header Name of the header
     * @param boolean $inherited
     * @return string|array Value of the property when a header has been
     * provided, all the header properties in an array otherwise
     */
    public function getHeader($locale, $header = null, $inherited = true) {
        $prefix = self::PROPERTY_HEADER . '.' . $locale . '.';

        if ($header) {
            return $this->get($prefix . strtolower($header), null, $inherited);
        }

        $prefixLength = strlen($prefix);

        $parentNode = $this->getParentNode();
        if ($inherited && $parentNode) {
            $headers = $parentNode->getHeader($locale, null, true);
        } else {
            $headers = array();
        }

        foreach ($this->properties as $key => $property) {
            if (strpos($key, $prefix) !== 0) {
                continue;
            }

            $headers[str_replace($prefix, '', $key)] = $property->getValue();
        }

        return $headers;
    }

    /**
     * Sets the context of the node during dispatch
     * @param string|array $context Name of the context variable or an array
     * of key-value pairs for full context. A dotted key will be split in a
     * hierarchic array
     * @param mixed $value Context value
     * @return null
     */
    public function setContext($context, $value = null) {
        if (is_array($context)) {
            foreach ($context as $key => $value) {
                $this->setContext($key, $value);
            }

            return;
        }

        $data = &$this->context;

        $tokens = explode('.', $context);
        $numTokens = count($tokens);
        for ($index = 0; $index < $numTokens; $index++) {
            $token = $tokens[$index];
            if ($index == $numTokens - 1) {
                $dataKey = $token;

                break;
            }

            if (isset($data[$token]) && is_array($data[$token])) {
                $data = &$data[$token];
            } else {
                $data[$token] = array();
                $data = &$data[$token];
            }
        }

        if ($value === null) {
            if (isset($data[$dataKey])) {
                unset($data[$dataKey]);
            }
        } else {
            $data[$dataKey] = $value;
        }
    }

    /**
     * Gets the context of the node during dispatch
     * @param string $name Name of the context variable, a dotted key will be
     * looked up in a hierarchic array
     * @param mixed $default Default value for when the variable is not set
     * @return mixed Full context if no arguments provided, value of the
     * variable if set in the context, provided default value otherwise
     */
    public function getContext($name = null, $default = null) {
        if ($name === null) {
            return $this->context;
        }

        $tokens = explode('.', $name);
        if (count($tokens) === 1) {
            if (empty($this->context[$name])) {
                return $default;
            }

            return $this->context[$name];
        }

        $result = &$this->context;
        foreach ($tokens as $token) {
            if (!isset($result[$token])) {
                return $default;
            }

            $result = &$result[$token];
        }

        return $result;
    }

    /**
     * Sets the next time the published state changes
     * @param integer|null $dateExpires Timestamp of the change or null when no
     * change is coming
     * @return null
     */
    public function setDateExpires($dateExpires) {
        if ($dateExpires === null || $dateExpires < time()) {
            return;
        }

        if ($this->dateExpires === null || $dateExpires < $this->dateExpires) {
            $this->dateExpires = $dateExpires;
        }
    }

    /**
     * Gets the next time the published state changes
     * @return integer|null A timestamp of the change or null when no change is
     * coming
     */
    public function getDateExpires() {
        return $this->dateExpires;
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

    /**
     * Gets the sections in the provided region
     * @param string $region Name of the region
     * @return array Array with the name of the section as key and the name of
     * the layout as value
     */
    public function getSections($region) {
        $sections = array();

        $sectionsProperty = $this->get(self::PROPERTY_REGION . '.' . $region . '.' . self::PROPERTY_SECTIONS);
        if ($sectionsProperty === null) {
            return $sections;
        }

        $sectionNames = explode(NodeProperty::LIST_SEPARATOR, $sectionsProperty);
        foreach ($sectionNames as $sectionName) {
            $sectionName = trim($sectionName);

            $sections[$sectionName] = $this->getSectionLayout($region, $sectionName);
        }

        return $sections;
    }

    /**
     * Adds a section to the provided region
     * @param string $region Name of the region
     * @param string $layout Name of the layout
     * @param string $name Name for the section
     * @return string Name of the new section
     * @throws \ride\library\cms\exception\CmsException when the provided
     * section already exists
     */
    public function addSection($region, $layout, $name = null) {
        $sections = $this->getSections($region);
        if ($name !== null && isset($sections[$name])) {
            throw new CmsException('Could not add section: ' . $name . ' already exists, use setSectionLayout to change the layout.');
        }

        if ($name === null) {
            $name = count($sections);
            while (isset($sections[$name])) {
                $name++;
            }
        }

        $sections[$name] = $layout;

        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . $name . '.layout', $layout);
        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . self::PROPERTY_SECTIONS, implode(NodeProperty::LIST_SEPARATOR, array_keys($sections)));

        return $name;
    }

    /**
     * Deletes a section from the provided region
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @return null
     */
    public function deleteSection($region, $section) {
        $sections = $this->getSections($region);
        if (!isset($sections[$section])) {
            throw new CmsException('Could not delete section: ' . $name . ' does not exist');
        }

        // remove from sections definition
        unset($sections[$section]);
        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . self::PROPERTY_SECTIONS, implode(NodeProperty::LIST_SEPARATOR, array_keys($sections)));

        // remove actual section definition
        $prefix = self::PROPERTY_REGION . '.' . $region . '.' . $section . '.';

        $properties = $this->getProperties($prefix);
        foreach ($properties as $key => $property) {
            unset($this->properties[$key]);
        }
    }

    /**
     * Sets the section layout
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $layout Name of the layout
     * @return null
     */
    public function setSectionLayout($region, $section, $layout) {
        $sections = $this->getSections($region);
        if (!isset($sections[$section])) {
            throw new CmsException('Could not set layout of section: ' . $section . ' does not exist.');
        }

        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_LAYOUT, $layout);
    }

    /**
     * Gets the section layout
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $default Default value for when no layout set
     * @return string Name of the layout
     */
    public function getSectionLayout($region, $section, $default = null) {
        return $this->get(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_LAYOUT, $default);
    }

    /**
     * Sets the section title
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $locale Code of the locale
     * @param string $title Title for the section
     * @return null
     */
    public function setSectionTitle($region, $section, $locale, $title) {
        $sections = $this->getSections($region);
        if (!isset($sections[$section])) {
            throw new CmsException('Could not set style of section: ' . $section . ' does not exist.');
        }

        $this->setLocalized($locale, self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_TITLE, $title);
    }

    /**
     * Gets the section title
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $locale Code of the locale
     * @param string $default Default value for when no title set
     * @return string Title for the section
     */
    public function getSectionTitle($region, $section, $locale, $default = null) {
        return $this->getLocalized($locale, self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_TITLE, $default);
    }

    /**
     * Sets whether the section uses the full width
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param boolean $isFullWidth
     * @return null
     */
    public function setIsSectionFullWidth($region, $section, $isFullWidth) {
        $sections = $this->getSections($region);
        if (!isset($sections[$section])) {
            throw new CmsException('Could not set properties of section: ' . $section . ' does not exist.');
        }

        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_FULL_WIDTH, $isFullWidth);
    }

    /**
     * Gets whether the section uses the full width
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $default Default value for when no title set
     * @return string Whether the section uses full width
     */
    public function isSectionFullWidth($region, $section, $default = null) {
        return $this->get(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_FULL_WIDTH, $default);
    }

    /**
     * Sets the section style
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $style Extra style class for the section
     * @return null
     */
    public function setSectionStyle($region, $section, $style) {
        $sections = $this->getSections($region);
        if (!isset($sections[$section])) {
            throw new CmsException('Could not set style of section: ' . $section . ' does not exist.');
        }

        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_STYLE, $style);
    }

    /**
     * Gets the section style
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $default Default value for when no layout set
     * @return string Extra style class for the section
     */
    public function getSectionStyle($region, $section, $default = null) {
        return $this->get(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_STYLE, $default);
    }

    /**
     * Orders the sections in the provided region
     * @param string $region Name of the region
     * @param array $order Array with the section name as key and as value an
     * array with the block name as key and an array of widget ids as value
     * @return null
     * @throw \ride\library\cms\exception\CmsException when the order could not
     * be performed
     */
    public function orderSections($region, array $order) {
        $sections = $this->getSections($region);

        // validate sections
        foreach ($order as $section => $blocks) {
            if (!isset($sections[$section])) {
                throw new CmsException('Could not order sections of ' . $region . ': ' . $section . ' does not exist.');
            }

            unset($sections[$section]);
        }

        if (count($sections)) {
            throw new CmsException('Could not order sections of ' . $region . ': not all sections provided. Missing sections are ' . implode(', ', array_keys($sections)) . '.');
        }

        // update section order
        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . self::PROPERTY_SECTIONS, implode(NodeProperty::LIST_SEPARATOR, array_keys($order)));

        // update widget order
        foreach ($order as $section => $blocks) {
            foreach ($blocks as $block => $widgets) {
                $this->orderWidgets($region, $section, $block, $widgets);
            }
        }
    }

    /**
     * Order the widgets of a region
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $block Name of the block
     * @param string|array $widgets Array with widget ids or a string with
     * widget ids separated by a comma.
     * @return null
     * @throws \ride\library\cms\exception\CmsException when the widgets could
     * not be ordered
     */
    public function orderWidgets($region, $section, $block, $widgets) {
        if (!is_array($widgets)) {
            if (!$widgets) {
                $widgets = array();
            } else {
                $widgets = explode(NodeProperty::LIST_SEPARATOR, $widgets);
            }
        }

        $sectionWidgets = $this->getSectionWidgets($this, $region, $section, false);
        $sectionWidgets[$block] = array_flip($widgets);

        $this->setSectionWidgets($region, $section, $sectionWidgets);
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
     * Gets a widget properties for the provided widget
     * @param integer $widgetId Id of the widget
     * @return \ride\library\cms\widget\NodeWidgetProperties
     */
    public function getWidgetProperties($widgetId) {
        return new NodeWidgetProperties($this, $widgetId);
    }

    /**
     * Gets the block information for a widget
     * @param string $widgetId Id of the widget instance
     * @return array|null Null if the widget is not set on this node, an array
     * with the region, section and block as key otherwise
     */
    public function getWidgetBlockInfo($widgetId) {
        $prefix = self::PROPERTY_REGION . '.';
        $suffix = '.' . self::PROPERTY_WIDGETS;

        $site = $this->getRootNode();

        foreach ($this->properties as $key => $property) {
            if (strpos($key, $prefix) !== 0 || !strpos($key, $suffix)) {
                // not a region widgets definition
                continue;
            }

            $tokens = explode('.', $key);

            $widgets = $this->parseSectionString($site, $property->getValue());
            foreach ($widgets as $block => $blockWidgets) {
                if (isset($blockWidgets[$widgetId])) {
                    return array(
                        'region' => $tokens[1],
                        'section' => $tokens[2],
                        'block' => $block,
                    );
                }
            }
        }

        return null;
    }

    /**
     * Get the widgets for a section in a region
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @return array Array with the block id as key and as value an array with the
     * widget instance id as key and the widget id as value
     */
    public function getWidgets($region, $section) {
        return $this->getSectionWidgets($this, $region, $section, false);
    }

    /**
     * Get the inherited widgets for a region
     * @param string $region Name of the region
     * @return array Array with the widget id as key and value
     */
    public function getInheritedWidgets($region, $section) {
        $widgets = array();

        if (!$this->hasParent()) {
            return $widgets;
        }

        $node = $this->getParentNode();

        do {
            $nodeWidgets = $this->getSectionWidgets($node, $region, $section, true);
            foreach ($nodeWidgets as $block => $blockWidgets) {
                if (isset($widgets[$block]) && $widgets[$block]) {
                    continue;
                }

                $widgets[$block] = $blockWidgets;
            }

            $allSectionsSet = true;
            foreach ($widgets as $block => $blockWidgets) {
                if (!$blockWidgets) {
                    $allSectionsSet = false;

                    break;
                }
            }

            if ($allSectionsSet) {
                break;
            }

            $node = $node->getParentNode();
        } while ($node);

        return $widgets;
    }

    /**
     * Adds a widget to a section
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param string $block Name of the block
     * @param integer $widgetId Id of the new widget instance
     * @return null
     */
    public function addWidget($region, $section, $block, $widgetId) {
        $sectionWidgets = $this->getSectionWidgets($this, $region, $section, false);
        if (isset($sectionWidgets[$block][$widgetId])) {
            return;
        }

        if (isset($sectionWidgets[$block])) {
            $sectionWidgets[$block][$widgetId] = $widgetId;
        } else {
            $sectionWidgets[$block] = array($widgetId => $widgetId);
        }

        $this->setSectionWidgets($region, $section, $sectionWidgets);
    }

    /**
     * Deletes a widget from a region
     * @param string $region Name of the region
     * @param int $id Id of the widget instance
     * @return null
     * @throws \ride\library\cms\exception\CmsException when a widget could not
     * be found
     */
    public function deleteWidget($region, $section, $block, $widgetId) {
        $sectionWidgets = $this->getSectionWidgets($this, $region, $section, false);
        if (!isset($sectionWidgets[$block][$widgetId])) {
            throw new CmsException('Could not delete widget with id ' . $widgetId . ': widget not found');
        }

        $properties = $this->getWidgetProperties($widgetId);
        $properties->clearWidgetProperties();

        unset($sectionWidgets[$block][$widgetId]);

        $this->setSectionWidgets($region, $section, $sectionWidgets);
    }

    /**
     * Gets the used widgets of this node
     * @return array Array with the widget instance id as key and value
     */
    public function getUsedWidgets() {
        $widgets = array();

        $prefix = self::PROPERTY_REGION . '.';
        $suffix = '.' . self::PROPERTY_WIDGETS;

        $site = $this->getRootNode();

        foreach ($this->properties as $key => $property) {
            if (strpos($key, $prefix) !== 0 || !strpos($key, $suffix)) {
                // not a region widgets definition
                continue;
            }

            $sectionWidgets = $this->parseSectionString($site, $property->getValue());
            foreach ($sectionWidgets as $block => $blockWidgets) {
                foreach ($blockWidgets as $widgetId => $widget) {
                    $widgets[$widgetId] = $widgetId;
                }
            }
        }

        return $widgets;
    }

    /**
     * Get the widgets for a section in a region for the provided node
     * @param Node $node Node to query
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param boolean $inherited Fetch only inherited properties of the node
     * @return array Array with the block id as key and as value an array with the
     * widget instance id as key and the widget id as value
     */
    protected function getSectionWidgets(Node $node, $region, $section, $inherited) {
        // resolve set widgets
        $sectionString = $node->get(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_WIDGETS, null, true, $inherited);
        if (!$sectionString) {
            return array();
        }

        // parse widget string
        $sectionWidgets = $this->parseSectionString($node->getRootNode(), $sectionString);
        if ($inherited) {
            return $sectionWidgets;
        }

        // resolve inherited widgets
        $inheritedSectionWidgets = $this->getInheritedWidgets($region, $section);

        foreach ($sectionWidgets as $blockId => $blockWidgets) {
            if (!$blockWidgets && isset($inheritedSectionWidgets[$blockId])) {
                $sectionWidgets[$blockId] = $inheritedSectionWidgets[$blockId];
            }
        }

        return $sectionWidgets;
    }

    /**
     * Sets the section widget array back to the node properties
     * @param string $region Name of the region
     * @param string $section Name of the section
     * @param array $widgets Array with the block id as key and as value an
     * array with the widget instance id as key and the widget id as value
     */
    protected function setSectionWidgets($region, $section, array $widgets) {
        $widgetBlocks = array();
        foreach ($widgets as $blockWidgets) {
            $widgetBlocks[] = self::BLOCK_OPEN . implode(NodeProperty::LIST_SEPARATOR, array_keys($blockWidgets)) . self::BLOCK_CLOSE;
        }

        $this->set(self::PROPERTY_REGION . '.' . $region . '.' . $section . '.' . self::PROPERTY_WIDGETS, implode(NodeProperty::LIST_SEPARATOR, $widgetBlocks));
    }

    /**
     * Parses the widget from the provided section string
     * @param string $section String to define the widgets in a section
     * eg. [12,4,87],[43,2]
     * @return array Array with the block id as key and as value an array with the
     * widget instance id as key and the widget id as value
     */
    public function parseSectionString(Node $site, $sectionString) {
        $blocks = array();

        $blocksWidgetIds = explode(self::BLOCK_CLOSE . NodeProperty::LIST_SEPARATOR . self::BLOCK_OPEN, $sectionString);
        foreach ($blocksWidgetIds as $block => $blockWidgetIds) {
            $block++; // start from 1 instead of 0

            $blocks[$block] = array();

            $blockWidgetIds = trim($blockWidgetIds, self::BLOCK_OPEN . self::BLOCK_CLOSE . ' ');
            if (!$blockWidgetIds) {
                continue;
            }

            $widgetIds = explode(NodeProperty::LIST_SEPARATOR, $blockWidgetIds);
            foreach ($widgetIds as $widgetId) {
                $widgetId = trim($widgetId);

                $blocks[$block][$widgetId] = $site->get(self::PROPERTY_WIDGET . '.' . $widgetId);
            }
        }

        return $blocks;
    }

    /**
     * Parses a global id into a site id and a node id
     * @param string $globalId Global node id
     * @param string $siteId Parsed site id
     * @param string $nodeId Parsed node id
     * @return boolean True when valid id, false otherwise
     */
    public static function parseGlobalId($globalId, &$siteId = null, &$nodeId = null) {
        if (substr_count($globalId, self::PATH_SEPARATOR) != 1) {
            return false;
        }

        list($siteId, $nodeId) = explode(self::PATH_SEPARATOR, $globalId);

        return true;
    }

}
