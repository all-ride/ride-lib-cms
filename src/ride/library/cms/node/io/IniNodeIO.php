<?php

namespace ride\library\cms\node\io;

use ride\library\cms\expired\ExpiredRouteModel;
use ride\library\cms\exception\CmsException;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeProperty;
use ride\library\config\ConfigHelper;
use ride\library\system\file\File;

use \Exception;

/**
 * INI implementation of the NodeIO
 */
class IniNodeIO extends AbstractNodeIO {

    /**
     * Name of the type property
     * @var string
     */
    const PROPERTY_TYPE = 'type';

    /**
     * Name of the id property
     * @var string
     */
    const PROPERTY_ID = 'id';

    /**
     * Name of the parent property
     * @var string
     */
    const PROPERTY_PARENT = 'parent';

    /**
     * Name of the order property
     * @var string
     */
    const PROPERTY_ORDER = 'order';

    /**
     * Path for the node files
     * @var ride\library\system\file\File
     */
    protected $path;

    /**
     * Instance of the config helper
     * @var ride\library\config\ConfigHelper
     */
    protected $configHelper;

    /**
     * Instance of the expired route model
     * @var ride\library\cms\expired\ExpiredRouteModel
     */
    protected $expiredRouteModel;

    /**
     * Offset for the instance id of a new widget
     * @var integer
     */
    protected $widgetIdOffset;

    /**
     * Constructs a new ini node IO
     * @param \ride\library\system\file\File $path Path for the data files
     * @param \ride\library\config\ConfigHelper $configHelper Instance of the
     * configuration helper
     * @param \ride\library\cms\expired\ExpiredRouteModel $expiredRouteModel
     * Instance of the expired route model
     * @return null
     */
    public function __construct(File $path, ConfigHelper $configHelper, ExpiredRouteModel $expiredRouteModel) {
        $this->path = $path;
        $this->configHelper = $configHelper;
        $this->expiredRouteModel = $expiredRouteModel;
    }

    /**
     * Gets the path for the data files
     * @return \ride\library\system\file\File
     */
    public function getPath() {
        return $this->path;
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
     * Reads all the nodes from the data source
     * @return array Array with Node objects
     */
    protected function readNodes() {
        $this->nodes = array();

        if (!$this->path->exists()) {
            return $this->nodes;
        }

        $directories = $this->path->read();
        foreach ($directories as $directory) {
            if (!$directory->isDirectory()) {
                continue;
            }

            $files = $directory->read();
            foreach ($files as $file) {
                if ($file->isDirectory() || $file->getExtension() != 'ini') {
                    continue;
                }

                try {
                    $ini = $file->read();
                    $node = $this->getNodeFromIni($ini);
                } catch (Exception $exception) {
                    throw new CmsException('Could not parse the INI configuration from ' . $file->getName(), 0, $exception);
                }

                $this->nodes[$node->getId()] = $node;
            }
        }

        // set the parent node instances
        foreach ($this->nodes as $node) {
            $parentId = $node->getParentNodeId();
            if (!$parentId) {
                // site node
                $node->setWidgetIdOffset($this->widgetIdOffset);

                continue;
            }

            if (isset($this->nodes[$parentId])) {
                $node->setParentNode($this->nodes[$parentId]);
            } else {
                $rootId = $node->getRootNodeId();
                if (isset($this->nodes[$rootId])) {
                    $node->setParentNode($this->nodes[$rootId]);
                }
            }
        }

        return $this->nodes;
    }

    /**
     * Gets a node from a INI string
     * @param string $ini
     * @return ride\library\cms\node\Node
     */
    protected function getNodeFromIni($ini) {
        $ini = $this->parseIni($ini);

        if (!isset($ini[self::PROPERTY_ID])) {
            throw new CmsException('No id provided for the node');
        }
        $id = $ini[self::PROPERTY_ID];
        unset($ini[self::PROPERTY_ID]);

        if (!isset($ini[self::PROPERTY_TYPE])) {
            throw new JoppaException('No type provided for node ' . $id);
        }
        $type = $ini[self::PROPERTY_TYPE];
        unset($ini[self::PROPERTY_TYPE]);

        if (!isset($ini[self::PROPERTY_PARENT])) {
            $parent = null;
            $order = null;
        } else {
            $parent = $ini[self::PROPERTY_PARENT];
            unset($ini[self::PROPERTY_PARENT]);

            if (isset($ini[self::PROPERTY_ORDER])) {
                $orderIndex = $ini[self::PROPERTY_ORDER];
                unset($ini[self::PROPERTY_ORDER]);
            } else {
                $orderIndex = 1;
            }
        }

        $node = $this->nodeModel->createNode($type);
        $node->setId($id);

        if ($parent) {
            $node->setParent($parent);
            $node->setOrderIndex($orderIndex);
        }

        $inheritPrefixLength = strlen(NodeProperty::INHERIT_PREFIX);
        foreach ($ini as $key => $value) {
            $inherit = false;

            if (strpos($key, NodeProperty::INHERIT_PREFIX) === 0) {
                $key = substr($key, $inheritPrefixLength);
                $inherit = true;
            }

            $node->set($key, $value, $inherit);
        }

        return $node;
    }

    /**
     * Parse the INI in a array
     * @param string $ini INI contents
     * @return array
     */
    protected function parseIni($ini) {
        $parsedIni = @parse_ini_string($ini, true);
        if ($parsedIni === false) {
            throw new CmsException('Could not parse ini: ' . $ini);
        }

        return $this->configHelper->flattenConfig($parsedIni);
    }

    /**
     * Writes a node into the data source
     * @param ride\library\cms\node\Node $node
     * @return null
     */
    protected function writeNode(Node $node) {
        $contents = $this->getIniFromNode($node);

        $nodeFile = $this->getNodeFile($node);
        if ($nodeFile->exists()) {
            $this->handleExpiredRoutes($nodeFile, $node);
        }

        $nodeFile->getParent()->create();
        $nodeFile->write($contents);

        // @todo clear cache
    }

    /**
     * Handles the expired routes
     * @param ride\library\system\file\File $nodeFile
     * @param ride\library\cms\node\Node $node
     * @return null
     */
    protected function handleExpiredRoutes(File $nodeFile, Node $node) {
        $ini = $nodeFile->read();
        $oldNode = $this->getNodeFromIni($ini);
        $oldRoutes = $oldNode->getRoutes();

        $routes = $node->getRoutes();

        $locales = array_keys($oldRoutes + $routes);
        foreach ($locales as $locale) {
            $routeSet = isset($routes[$locale]);
            $oldRouteSet = isset($oldRoutes[$locale]);

            if ($routeSet && $oldRouteSet) {
                if ($routes[$locale] == $oldRoutes[$locale]) {
                    continue;
                }

                $this->expiredRouteModel->removeExpiredRoutesByPath($routes[$locale]);
                $this->expiredRouteModel->addExpiredRoute($node->getId(), $locale, $oldRoutes[$locale], $node->getRootNode()->getBaseUrl($locale));
            } elseif (!$routeSet && $oldRouteSet) {
                $this->expiredRouteModel->addExpiredRoute($node->getId(), $locale, $oldRoutes[$locale], $node->getRootNode()->getBaseUrl($locale));
            }
        }
    }

    /**
     * Gets the ini string of a node
     * @param ride\library\cms\node\Node $node
     * @return string
     */
    protected function getIniFromNode(Node $node) {
        $properties = array();
        $properties[self::PROPERTY_TYPE] = new NodeProperty(self::PROPERTY_TYPE, $node->getType());
        $properties[self::PROPERTY_ID] = new NodeProperty(self::PROPERTY_ID, $node->getId());
        $properties[self::PROPERTY_PARENT] = new NodeProperty(self::PROPERTY_PARENT, $node->getParent());
        $properties[self::PROPERTY_ORDER] = new NodeProperty(self::PROPERTY_ORDER, $node->getOrderIndex());
        $properties += $node->getProperties();

        $ini = '';
        foreach ($properties as $property) {
            $ini .= $property->getIniString() . "\n";
        }

        return $ini;
    }

    /**
     * Deletes a node
     * @param ride\library\cms\node\Node $node
     * @return null
     */
    protected function deleteNode(Node $node) {
        $rootNodeId = $node->getRootNodeId();
        $nodeId = $node->getId();

        if ($rootNodeId == $nodeId) {
            $nodeFile = $this->path->getChild($rootNodeId);
        } else {
            $nodeFile = $this->getNodeFile($node);
        }

        if ($nodeFile->exists()) {
            $nodeFile->delete();
        }

        $this->expiredRouteModel->removeExpiredRoutesByNode($nodeId);
    }

    /**
     * Gets the file for the node
     * @param string $nodeId Id of the node
     * @return ride\library\system\file\File
     */
    protected function getNodeFile($node) {
        $rootNodeId = $node->getRootNodeId();
        $nodeId = $node->getId();

        return $this->path->getChild($rootNodeId . '/' . $nodeId . '.ini');
    }

}