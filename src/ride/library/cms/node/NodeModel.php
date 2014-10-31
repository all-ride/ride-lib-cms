<?php

namespace ride\library\cms\node;

use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\io\NodeIO;
use ride\library\cms\node\type\NodeTypeManager;
use ride\library\cms\node\type\SiteNodeType;
use ride\library\cms\node\validator\NodeValidator;
use ride\library\cms\node\SiteNode;
use ride\library\event\EventManager;

/**
 * Model for the nodes
 */
class NodeModel {

    /**
     * Name of the event before a node is being saved or removed
     * @var string
     */
    const EVENT_PRE_ACTION = 'cms.node.action.pre';

    /**
     * Name of the event after a node is being saved or removed
     * @var string
     */
    const EVENT_POST_ACTION = 'cms.node.action.post';

    /**
     * Facade of the node types
     * @var \ride\library\cms\node\type\NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * Facade of the node types
     * @var \ride\library\cms\node\io\NodeIO
     */
    protected $io;

    /**
     * Validator for the node properties
     * @var \ride\library\cms\node\validator\NodeValidator
     */
    protected $validator;

    /**
     * Instance of the event manager
     * @var \ride\library\event\EventManager
     */
    protected $eventManager;

    /**
     * Name of the default revision
     * @var string
     */
    protected $defaultRevision;

    /**
     * Name of the draft  revision
     * @var string
     */
    protected $draftRevision;

    /**
     * Creates a new node model
     * @param \ride\library\cms\node\type\NodeTypeManager $nodeTypeManager
     * @param \ride\library\cms\node\io\NodeIO $io
     * @return null
     */
    public function __construct(NodeTypeManager $nodeTypeManager, NodeIO $io, NodeValidator $validator) {
        $io->setNodeModel($this);

        $this->nodeTypeManager = $nodeTypeManager;
        $this->io = $io;
        $this->validator = $validator;

        $this->defaultRevision = 'master';
        $this->draftRevision = 'draft';
    }

    /**
     * Sets the event manager to the node model
     * @param \ride\library\event\EventManager $eventManager
     * @return null
     */
    public function setEventManager(EventManager $eventManager) {
        $this->eventManager = $eventManager;
    }

    /**
     * Gets the facade for the node types
     * @return \ride\library\cms\node\type\NodeTypeManager
     */
    public function getNodeTypeManager() {
        return $this->nodeTypeManager;
    }

    /**
     * Sets the default revision
     * @param string $defaultRevision Name of the default revision
     * @return null
     */
    public function setDefaultRevision($defaultRevision) {
        if (!is_string($defaultRevision) || !$defaultRevision) {
            throw new CmsException('Could not set the default revision: no string or empty value provided');
        }

        $this->defaultRevision = $defaultRevision;
    }

    /**
     * Gets the default revision
     * @return string
     */
    public function getDefaultRevision() {
        return $this->defaultRevision;
    }

    /**
     * Sets the draft revision
     * @param string $draftRevision Name of the draft revision
     * @return null
     */
    public function setDraftRevision($draftRevision) {
        if (!is_string($draftRevision) || !$draftRevision) {
            throw new CmsException('Could not set the draft revision: no string or empty value provided');
        }

        $this->draftRevision = $draftRevision;
    }

    /**
     * Gets the draft revision
     * @return string
     */
    public function getDraftRevision() {
        return $this->draftRevision;
    }

    /**
     * Gets all the available sites
     * @return array Array with the id of the site as key and the SiteNode as
     * value
     */
    public function getSites() {
        return $this->io->getSites();
    }

    /**
     * Gets a site
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return SiteNode
     */
    public function getSite($siteId, $revision, $children = false, $depth = false) {
        return $this->io->getSite($siteId, $revision, $children, $depth);
    }

    /**
     * Gets the current site based on the URL
     * @param string $baseUrl Base URL to get a site for
     * @param string locale Resolved locale will be store in this variable
     * @return SiteNode|null
     */
    public function getCurrentSite($baseUrl, &$locale = null) {
        $sites = $this->getSites();
        foreach ($sites as $siteId => $site) {
            $locale = $site->getLocaleForBaseUrl($baseUrl);
            if ($locale !== null) {
                if (!$site->hasLocalizedBaseUrl()) {
                    $locale = null;
                }

                return $site;
            }

            if (!$site->isPublished()) {
                unset($sites[$siteId]);
            }
        }

        if (count($sites) === 1) {
            return reset($sites);
        }

        throw new NodeNotFoundException();
    }

    /**
     * Gets a node
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $nodeId Id of the node
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\Node
     * @throws \ride\library\cms\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($siteId, $revision, $nodeId, $type = null, $children = false, $depth = false) {
        return $this->io->getNode($siteId, $revision, $nodeId, $type, $children, $depth);
    }

    /**
     * Gets all the nodes
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array
     */
    public function getNodes($siteId, $revision) {
        return $this->io->getNodes($siteId, $revision);
    }

    /**
     * Gets all the nodes of a certain type
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $type Name of the type
     * @return array
     */
    public function getNodesByType($siteId, $revision, $type) {
        return $this->io->getNodesByType($siteId, $revision, $type);
    }

    /**
     * Gets all the nodes for a specific path
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $path Materialized path for the nodes
     * @return array Array with Node instances
     */
    public function getNodesByPath($siteId, $revision, $path) {
        return $this->io->getNodesByPath($siteId, $revision, $path);
    }

    /**
     * Gets all the nodes which contain a certain widget
     * @param string $widget Name of the widget (dependency id)
     * @param string $site Id of the site node
     * @return array
     */
    public function getNodesForWidget($widget, $site = null, $locale = null) {
        $nodes = $this->io->getNodes();

        if ($site && !isset($nodes[$site])) {
            throw new NodeNotFoundException($site);
        }

        if ($site) {
            $sites = array($site => $nodes[$site]);
        } else {
            $sites = $this->getNodesByType('site');
        }

        $availableWidgets = array();

        foreach ($sites as $site) {
            $widgetIds = $site->getAvailableWidgets();
            foreach ($widgetIds as $widgetId => $widgetType) {
                if ($widgetType != $widget) {
                    continue;
                }

                $availableWidgets[$widgetId] = $widgetType;
            }
        }

        if (!$availableWidgets) {
            return array();
        }

        $result = array();

        foreach ($nodes as $index => $node) {
            if (!isset($sites[$node->getRootNodeId()])) {
                unset($nodes[$index]);

                continue;
            }

            if ($locale && !$node->isAvailableInLocale($locale)) {
                unset($nodes[$index]);

                continue;
            }

            $found = false;

            $properties = $node->getProperties();
            foreach ($properties as $key => $property) {
                if (strpos($key, Node::PROPERTY_WIDGETS . '.') !== 0) {
                    continue;
                }

                $nodeWidgetIds = explode(NodeProperty::LIST_SEPARATOR, $property->getValue());
                foreach ($nodeWidgetIds as $widgetId) {
                    $widgetId = trim($widgetId);

                    if (!isset($availableWidgets[$widgetId])) {
                        continue;
                    }

                    $resultNode = clone $node;
                    $resultNode->setWidgetId($widgetId);

                    $result[] = $resultNode;
                }
            }
        }

        return $result;
    }

    /**
     * Creates a new node
     * @param string $type Name of the node type
     * @param string $parentNodeId Id of the parent node
     * @return Node
     */
    public function createNode($type, $parentNodeId = null) {
        $node = $this->nodeTypeManager->getNodeType($type)->createNode();

        if ($parentNodeId) {
            $node->setParentNode($this->getNode($parentNodeId));
        }

        return $node;
    }

    /**
     * Validates a node
     * @param \ride\library\cms\node\Node $node The node to validate
     * @return null
     * @throws \ride\library\validation\exception\ValidationException when the
     * node is invalid
     */
    public function validateNode(Node $node) {
        $this->validator->validateNode($node, $this);

        $nodeType = $this->nodeTypeManager->getNodeType($node->getType());
        if ($nodeType instanceof NodeValidator) {
            $nodeType->validateNode($node, $this);
        }
    }

    /**
     * Saves a node to the data source
     * @param Node $node
     * @param string $description Description of the save action
     * @param boolean $autoPublish Set to false to skip auto publishing
     * @return null
     */
    public function setNode(Node $node, $description = null, $autoPublish = true) {
        $this->validateNode($node);

        if ($node->getRevision() === $this->defaultRevision) {
            $node->setRevision($this->draftRevision);
        }

        if ($this->eventManager) {
            if (!$description) {
                $description = 'Saved node ' . $node->getName();
            }

            $eventArguments = array(
                'action' => 'save',
                'nodes' => array($node),
                'description' => $description,
            );

            $this->eventManager->triggerEvent(self::EVENT_PRE_ACTION, $eventArguments);
        }

        $this->io->setNode($node);

        if ($this->eventManager) {
            $this->eventManager->triggerEvent(self::EVENT_POST_ACTION, $eventArguments);
        }

        if ($autoPublish && $node->getRootNode()->isAutoPublish()) {
            $this->publishNode($node);
        }
    }

    /**
     * Removes a node
     * @param \ride\library\cms\node\Node $node Node to remove
     * @param boolean $recursive Flag to see if child nodes should be deleted
     * @param string $description Description of the remove action
     * @param boolean $autoPublish Set to false to skip auto publishing
     * @return
     */
    public function removeNode(Node $node, $recursive = true, $description = null, $autoPublish = true) {
        if ($node->getRevision() === $this->defaultRevision && $node->getLevel() !== 0) {
            $node->setRevision($this->draftRevision);
        }

        if ($this->eventManager) {
            if (!$description) {
                $description = 'Removed node ' . $node->getName();
            }

            $eventArguments = array(
                'action' => 'remove',
                'nodes' => array($node),
                'description' => $description,
            );

            $this->eventManager->triggerEvent(self::EVENT_PRE_ACTION, $eventArguments);
        }

        $this->io->removeNode($node, $recursive);

        if ($this->eventManager) {
            $this->eventManager->triggerEvent(self::EVENT_POST_ACTION, $eventArguments);
        }

        if ($autoPublish && $node->getRootNode()->isAutoPublish()) {
            $this->publishNode($node);
        }
    }

    /**
     * Clones a node
     * @param \ride\library\cms\node\Node $node Node to clone
     * @param boolean $recursive Set to true to also clone the children of the
     * node
     * @param boolean $reorder Set to false to just clone the order index
     * instead of adding the cloned node after the source node
     * @param boolean $keepOriginalName Set to true to keep the name untouched,
     * else a suffix like (clone) or (clone 2, 3 ...) will be added to the name
     * of the clone
     * @param boolean $cloneRoutes Set to true to clone the routes of the nodes.
     * This will only work when copying a root node, else a validation error
     * will occur
     * @param boolean $newParent Provide a new parent for the clone, needed for
     * recursive cloning
     * @param boolean $autoPublish Set to false to skip auto publishing
     * @return null
     */
    public function cloneNode(Node $node, $recursive = true, $reorder = true, $keepOriginalName = false, $cloneRoutes = null, $newParent = null, $autoPublish = true) {
        $id = $node->getId();
        $rootNodeId = $node->getRootNodeId();

        if ($id == $rootNodeId) {
            $this->cloneTable = array();
        }

        if ($cloneRoutes === null) {
            if ($id == $rootNodeId) {
                $cloneRoutes = true;
            } else {
                $cloneRoutes = false;
            }
        }

        if ($newParent === null) {
            $this->widgetTable = array();
        }

        $nodeType = $this->nodeTypeManager->getNodeType($node->getType());
        $clone = $nodeType->createNode();

        if ($newParent) {
            $clone->setParent($newParent);
        } else {
            $clone->setParent($node->getParent());
        }

        if ($clone->getParent()) {
            $clone->setParentNode($this->io->getNode($clone->getParentNodeId()));
        }

        if ($reorder) {
            $clone->setOrderIndex($node->getOrderIndex() + 1);
        } else {
            $clone->setOrderIndex($node->getOrderIndex());
        }

        $this->cloneNodeProperties($node, $clone, $keepOriginalName, $cloneRoutes);

        $this->setNode($clone, 'Cloned ' . $node->getName(), false);

        if ($reorder) {
            // reorder the siblings after the original node
            $cloneOrderIndex = $clone->getOrderIndex();

            $siblings = $this->io->getChildren($node->getParent(), 0);
            foreach ($siblings as $sibling) {
                $siblingOrderIndex = $sibling->getOrderIndex();
                if ($siblingOrderIndex < $cloneOrderIndex) {
                    continue;
                }

                $sibling->setOrderIndex($siblingOrderIndex + 1);

                $this->setNode($sibling, 'Reordered ' . $sibling->getName() . ' after clone of ' . $node->getName(), false);
            }
        }

        if ($recursive) {
            // clone the children
            $children = $this->io->getChildren($node->getPath(), 0);

            $path = $clone->getPath();

            foreach ($children as $child) {
                $this->cloneNode($child, true, false, true, $cloneRoutes, $path, false);
            }
        }

        if (isset($this->cloneTable)) {
            $this->cloneTable[$id] = $clone->getId();
        }

        if ($id == $node->getRootNodeId()) {
            // we are cloning a site, update the node references in the properties
            $nodes = $this->getNodesByPath($clone->getId());
            foreach ($nodes as $node) {
                $hasChanged = false;

                $properties = $node->getProperties();
                foreach ($properties as $key => $property) {
                    if (substr($key, -5) != '.node') {
                        continue;
                    }

                    if (isset($this->cloneTable[$property->getValue()])) {
                        $property->setValue($this->cloneTable[$property->getValue()]);

                        $hasChanged = true;
                    }
                }

                if ($hasChanged) {
                    $this->setNode($node, "Updated node references for clone of " . $node->getName(), false);
                }
            }

            unset($this->cloneTable);
        }

        if ($newParent === null) {
            unset($this->widgetTable);
        }

        // save the root for newly created widgets
        $this->setNode($clone->getRootNode(), 'Updated widgets for clone of ' . $node->getName(), false);

        // perform auto publishing if enabled
        if ($autoPublish && $node->getRootNode()->isAutoPublish()) {
            $this->publishNode($node);
        }

        return $clone;
    }

    /**
     * Clones the node's properties to the destination
     * @param \ride\library\cms\node\Node $source Source node
     * @param \ride\library\cms\node\Node $destination Destination node
     * @param boolean $keepOriginalName Set to true to keep the name untouched
     * @param boolean $cloneRoutes Set to true to clone the routes of the nodes
     * @return null
     */
    protected function cloneNodeProperties(Node $source, Node $destination, $keepOriginalName, $cloneRoutes) {
        $widgetPropertyPrefixLength = strlen(Node::PROPERTY_WIDGET) + 1;

        $site = $destination->getRootNode();
        if (!$site) {
            $site = $destination;
        }

        $parent = $source->getParentNode();
        $sourceProperties = $source->getProperties();
        $destinationProperties = array();

        // duplicate the regions and the set widgets
        foreach ($sourceProperties as $index => $sourceProperty) {
            $key = $sourceProperty->getKey();

            if (strpos($key, Node::PROPERTY_WIDGETS) !== 0) {
                continue;
            }

            $newValue = '';
            $inheritedWidgetIds = array();

            if ($parent) {
                $inheritedValue = $parent->get($key);
                if ($inheritedValue) {
                    $inheritedWidgetIds = explode(NodeProperty::LIST_SEPARATOR, $inheritedValue);
                }
            }

            $sourceWidgetIds = explode(NodeProperty::LIST_SEPARATOR, $sourceProperty->getValue());
            foreach ($sourceWidgetIds as $widgetId) {
                if (isset($this->widgetTable[$widgetId])) {
                    $cloneWidgetId = $this->widgetTable[$widgetId];
                } elseif (in_array($widgetId, $inheritedWidgetIds)) {
                    // use the same widget id for inherited widgets
                    $cloneWidgetId = $widgetId;

                    $this->widgetTable[$widgetId] = $widgetId;
                } else {
                    // create a new widget for node widgets
                    $widget = $source->getWidget($widgetId);
                    $cloneWidgetId = $site->createWidget($widget);

                    $this->widgetTable[$widgetId] = $cloneWidgetId;
                }

                $newValue .= ($newValue ? NodeProperty::LIST_SEPARATOR : '') . $cloneWidgetId;
            }

            $destinationProperty = new NodeProperty($key, $newValue, $sourceProperty->getInherit());
            $destinationProperties[$key] = $destinationProperty;

            unset($sourceProperties[$index]);
        }

        // duplicate the remaining properties
        foreach ($sourceProperties as $index => $sourceProperty) {
            $key = $sourceProperty->getKey();
            $value = $sourceProperty->getValue();

            $destinationProperty = new NodeProperty($key, $value, $sourceProperty->getInherit());

            if (!$keepOriginalName && strpos($key, Node::PROPERTY_NAME . '.') === 0) {
                // add copy suffix to the name
                $locale = str_replace(Node::PROPERTY_NAME . '.', '', $key);
                $children = $this->io->getChildren($source->getParent(), 0);

                $baseName = $value;
                $name = $baseName . ' (clone)';
                $index = 1;

                do {
                    $found = false;

                    foreach ($children as $child) {
                        $childName = $child->getName();
                        if ($childName == $name) {
                            $name = $baseName . ' (clone ' . $index . ')';
                            $index++;

                            $found = true;
                            break;
                        }
                    }
                } while ($found);

                $destinationProperty->setValue($name);
            } elseif (!$cloneRoutes && strpos($key, Node::PROPERTY_ROUTE . '.') === 0) {
                // skip this property as it's a route and it's flagged to not clone routes
                continue;
            } elseif (strpos($key, Node::PROPERTY_WIDGET . '.') === 0) {
                // remap the widget ids for widget properties
                $propertyKey = substr($key, $widgetPropertyPrefixLength);
                $positionPropertySeparator = strpos($propertyKey, '.');
                if ($positionPropertySeparator === false) {
                    continue;
                }

                $widgetId = substr($propertyKey, 0, $positionPropertySeparator);
                $propertyKey = substr($propertyKey, $positionPropertySeparator);

                if (!isset($this->widgetTable[$widgetId])) {
                    continue;
                }

                $destinationProperty = new NodeProperty(
                    Node::PROPERTY_WIDGET . '.' . $this->widgetTable[$widgetId] . $propertyKey,
                    $destinationProperty->getValue(),
                    $destinationProperty->getInherit()
                );
            }

            $destinationProperties[$destinationProperty->getKey()] = $destinationProperty;
        }

        $destination->setProperties($destinationProperties);
    }

    /**
     * Reorder the nodes of a site
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param integer $parent Id of the parent node
     * @param array $nodeOrder Array with the node id as key and the number of children as value
     * @param boolean $autoPublish Set to false to skip auto publishing
     * @return null
     */
    public function orderNodes($siteId, $revision, $parent, array $nodeOrder, $locale, $autoPublish = true) {
        $parent = $this->io->getNode($siteId, $revision, $parent);
        $site = $parent->getRootNode();

        $path = $parent->getPath();
        $orderIndex = 1;
        $child = 0;

        $orderIndexes = array();
        $paths = array();
        $children = array();
        $saveNodes = array();

        $nodes = $this->io->getNodesByPath($siteId, $revision, $path);

        // process the provided order on the nodes
        foreach ($nodeOrder as $nodeId => $numChildren) {
            if (!isset($nodes[$nodeId])) {
                throw new CmsException('Could not order the nodes: Node with id ' . $nodeId . ' is not a child of node ' . $parent->getId());
            }

            $nodes[$nodeId]->setParent($path);
            $nodes[$nodeId]->setOrderIndex($orderIndex);

            $saveNodes[] = $nodes[$nodeId];

            $orderIndex++;

            if ($child) {
                $child--;

                if (!$child) {
                    $orderIndex = array_pop($orderIndexes);
                    $path = array_pop($paths);
                    $child = array_pop($children);
                }
            }

            if ($numChildren) {
                array_push($orderIndexes, $orderIndex);
                array_push($paths, $path);
                array_push($children, $child);

                $orderIndex = 1;
                $path = $nodes[$nodeId]->getPath();
                $child = $numChildren;
            }

            unset($nodes[$nodeId]);
        }

        // check remaining nodes
        if ($nodes) {
            if ($site->isLocalizationMethodUnique()) {
                $siblingOrder = array();

                foreach ($nodes as $nodeId => $node) {
                    if ($node->getParent() != $path || $node->isAvailableInLocale($locale)) {
                        continue;
                    }

                    $siblingOrder[$node->getOrderIndex()] = $node;

                    $id = $node->getId();
                    foreach ($nodes as $nodeId => $node) {
                        if ($nodeId === $id || $node->hasParent($id)) {
                            unset($nodes[$nodeId]);
                        }
                    }
                }

                ksort($siblingOrder);
                foreach ($siblingOrder as $sibling) {
                    $sibling->setOrderIndex($orderIndex);

                    $saveNodes[] = $sibling;

                    $orderIndex++;
                }
            }

            if ($nodes) {
                throw new CmsException('Could not order the nodes: not all nodes of the provided parent are provided in the node order array; missing nodes ' . implode(', ', array_keys($nodes)));
            }
        }

        // pre save event
        if ($this->eventManager) {
            $eventArguments = array(
                'action' => 'order',
                'nodes' => $saveNodes,
                'description' => 'Reordering nodes',
            );

            $this->eventManager->triggerEvent(self::EVENT_PRE_ACTION, $eventArguments);
        }

        // save changes
        foreach ($saveNodes as $node) {
            $this->io->setNode($node);
        }

        // post save event
        if ($this->eventManager) {
            $this->eventManager->triggerEvent(self::EVENT_POST_ACTION, $eventArguments);
        }

        // perform auto publishing if enabled
        if ($autoPublish && $site->isAutoPublish()) {
            $this->publishNode($parent);
        }
    }

    /**
     * Gets the breadcrumbs of a node
     * @param \ride\library\cms\node\Node $node
     * @param string $baseScript Base script for the node routes
     * @param string $locale Code of the locale
     * @return array Array with the URL as key and the node name as value
     */
    public function getBreadcrumbsForNode(Node $node, $baseScript, $locale) {
        $urls = array();

        if (!$node->hideInBreadcrumbs()) {
            $urls[$baseScript . $node->getRoute($locale)] = $node->getName($locale, 'breadcrumb');
        }

        $parent = $node->getParentNode();
        while ($parent) {
            $nodeType = $this->nodeTypeManager->getNodeType($parent->getType());
            if (($nodeType->getFrontendCallback() || $parent->getLevel() === 0) && !$parent->hideInBreadcrumbs()) {
                $url = $baseScript . $parent->getRoute($locale);
                $urls[$url] = $parent->getName($locale, 'breadcrumb');
            }

            $parent = $parent->getParentNode();
        }

        $urls = array_reverse($urls, true);

        return $urls;
    }

    /**
     * Create an array with the node hierarchy. Usefull for an options field.
     * @param array $nodes Array with Node objects
     * @param string $locale Code of the current locale
     * @param boolean $onlyFrontendNodes Flag to filter on frontend nodes
     * @param string $separator Separator between the node names
     * @param string $prefix Prefix for the node names
     * @return array Array with the node id as key and the node name as value
     */
    public function getListFromNodes(array $nodes, $locale, $onlyFrontendNodes = true, $separator = '/', $prefix = '') {
        $list = array();

        foreach ($nodes as $node) {
            $skip = false;

            if ($onlyFrontendNodes) {
                $nodeType = $this->nodeTypeManager->getNodeType($node->getType());
                if (!$nodeType->getFrontendCallback()) {
                    $skip = true;
                }
            }

            $newPrefix = $prefix . $separator . $node->getName($locale);

            if (!$skip) {
                $list[$node->getId()] = $newPrefix;
            }

            $children = $node->getChildren();
            if ($children) {
                $list += $this->getListFromNodes($children, $locale, $onlyFrontendNodes, $separator, $newPrefix);
            }
        }

        return $list;
    }

    /**
     * Gets the number of children levels for the provided node
     * @param \ride\library\cms\node\Node $node
     * @return integer
     */
    public function getChildrenLevels(Node $node) {
        $nodeLevel = $node->getLevel();

        $path = $node->getPath();
        $levels = 0;

        $nodes = $this->getNodesByPath($node->getRootNodeId(), $node->getRevision(), $path);
        foreach ($nodes as $node) {
            $parent = $node->getParent();
            $level = strlen($parent) - strlen(str_replace(Node::PATH_SEPARATOR, '', $parent)) + 1;
            $levels = max($levels, $level);
        }

        return $levels - $nodeLevel;
    }

    /**
     * Publish a node or site
     * @param \ride\library\cms\node\Node $node Node to publish
     * @param string $revision Name of the published revision, falls back to the
     * default revision
     * @param boolean $recursive Flag to see if the node's children should be
     * published as well
     * @return null
     */
    public function publishNode(Node $node, $revision = null, $recursive = true) {
        if ($revision === null) {
            $revision = $this->getDefaultRevision();
        }

        $this->io->publish($node, $revision, $recursive);
    }

    /**
     * Gets the trash of a site
     * @param string $siteId Id of the site
     * @return array Array with the id of the trash node as key and a TrashNode
     * instance as value
     */
    public function getTrashNodes($siteId) {
        return $this->io->getTrashNodes($siteId);
    }

    /**
     * Gets a node from the trash of a site
     * @param string $siteId Id of the site
     * @param string $trashNodeId Id of the trash node
     * @return \ride\library\cms\node\TrashNode
     * @throws \ride\library\cms\exception\NodeNotFoundException
     */
    public function getTrashNode($siteId, $trashNodeId) {
        return $this->io->getTrashNode($siteId, $trashNodeId);
    }

    /**
     * Restores the provided node or array of nodes
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param \ride\library\cms\node\TrashNode|array $trashNodes An instance of
     * TrashNode or an array of trash nodes
     * @param string $newParent Id of the new parent
     * @return null
     */
    public function restoreTrashNodes($siteId, $revision, $trashNodes, $newParent = null) {
        return $this->io->restoreTrashNodes($siteId, $revision, $trashNodes, $newParent);
    }

    /**
     * Cleans up all properties and widget instances of unused widgets
     * @return null
     */
    public function cleanUp() {
        $sites = $this->getSites();
        foreach ($sites as $siteId => $site) {
            $revisions = $site->getRevisions();
            foreach ($revisions as $revision) {
                $usedWidgets = $site->getAvailableWidgets();

                $nodes = $this->getNodesByPath($siteId, $revision, $siteId);

                // detect unused widgets
                $this->checkWidgetUsage($site->getProperties(), $usedWidgets);
                foreach ($nodes as $node) {
                    $this->checkWidgetUsage($node->getProperties(), $usedWidgets);
                }

                // clear unused widget properties
                foreach ($nodes as $node) {
                    if ($this->clearWidgetUsage($node, $usedWidgets)) {
                        $this->setNode($node, 'Cleaned up ' . $node->getName(), false);
                    }
                }

                if ($this->clearWidgetUsage($site, $usedWidgets)) {
                    $this->setNode($site, 'Cleaned up ' . $site->getName(), false);
                }
            }
        }
    }

    /**
     * Checks the usage of the widgets
     * @param array $properties Array with a NodeProperty as value and the
     * property name as key
     * @param array $usedWidgets Array with the widget instance id as key and
     * the widget id/name as value
     * @return null
     */
    protected function checkWidgetUsage(array $properties, array &$usedWidgets) {
        foreach ($properties as $key => $property) {
            if (strpos($key, Node::PROPERTY_WIDGETS . '.') !== 0 || substr_count($key, '.') !== 1) {
                continue;
            }

            $widgetIds = explode(',', $property->getValue());
            foreach ($widgetIds as $widgetId) {
                if (isset($usedWidgets[$widgetId])) {
                    unset($usedWidgets[$widgetId]);
                }
            }
        }
    }

    /**
     * Cleans up all properties in the provided node which are used one of the
     * provided widgets
     * @param \ride\library\cms\node\Node $node
     * @param array $unusedWidgetd Array with the widget instance id as key
     * @return boolean Flag to see if the node has changed
     */
    protected function clearWidgetUsage(Node $node, array $unusedWidgets) {
        $isChanged = false;

        $isRootNode = $node->getParent() ? false : true;
        $properties = $node->getProperties();

        foreach ($unusedWidgets as $widgetId => $null) {
            foreach ($properties as $key => $property) {
                if (strpos($key, Node::PROPERTY_WIDGET . '.' . $widgetId . '.') === 0 || ($isRootNode && $key === Node::PROPERTY_WIDGET . '.' . $widgetId)) {
                    $node->set($key, null);

                    $isChanged = true;
                }

            }
        }

        return $isChanged;
    }

}
