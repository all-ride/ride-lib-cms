<?php

namespace ride\library\cms\node\io;

use ride\library\cms\expired\ExpiredRouteModel;
use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\type\SiteNodeType;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeProperty;
use ride\library\cms\node\SiteNode;
use ride\library\cms\node\TrashNode;
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
     * @var \ride\library\system\file\File
     */
    protected $path;

    /**
     * Instance of the config helper
     * @var \ride\library\config\ConfigHelper
     */
    protected $configHelper;

    /**
     * Instance of the expired route model
     * @var \ride\library\cms\expired\ExpiredRouteModel
     */
    protected $expiredRouteModel;

    /**
     * Offset for the instance id of a new widget
     * @var integer
     */
    protected $widgetIdOffset;

    /**
     * Name of the special archive revision
     * @var string
     */
    protected $archiveName;

    /**
     * Name of the special trash revision
     * @var string
     */
    protected $trashName;

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

        $this->archiveName = '_archive';
        $this->trashName = '_trash';
        $this->sites = null;
        $this->nodes = null;
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
     * Reads the sites from the data source
     * @return array
     */
    protected function readSites() {
        $sites = array();

        if (!$this->path->exists()) {
            return $sites;
        }

        $defaultRevision = $this->nodeModel->getDefaultRevision();

        $siteDirectories = $this->path->read();
        foreach ($siteDirectories as $siteDirectory) {
            if (!$siteDirectory->isDirectory()) {
                continue;
            }

            $siteId = $siteDirectory->getName();
            if ($siteId[0] == '.') {
                // hidden directory
                continue;
            }

            $revisions = array();

            $files = $siteDirectory->read();
            foreach ($files as $file) {
                $revision = $file->getName();
                if ($revision === $this->archiveName || $revision === $this->trashName || !$file->isDirectory()) {
                    continue;
                }

                $revisions[$revision] = $revision;
            }

            if (!$revisions) {
                throw new CmsException('No valid site in ' . $siteDirectory->getName());
            }

            if (isset($revisions[$defaultRevision])) {
                $revision = $defaultRevision;
            } else {
                $revision = reset($revisions);
            }

            $siteFile = $siteDirectory->getChild($revision . '/' . $siteId . '.ini');
            if ($siteFile->exists()) {
                try {
                    $ini = $siteFile->read();
                    $site = $this->getNodeFromIni($ini);
                } catch (Exception $exception) {
                    throw new CmsException('Could not parse the INI configuration from ' . $siteFile->getName(), 0, $exception);
                }
            } else {
                throw new CmsException('No valid site in ' . $siteDirectory->getName());
            }

            $site->setRevisions($revisions);
            $site->setRevision($revision);

            $sites[$siteId] = $site;
        }

        return $sites;
    }

    /**
     * Reads all the nodes from the data source
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array Array with Node objects
     */
    protected function readNodes($siteId, $revision) {
        $nodes = array();

        $directory = $this->path->getChild($siteId . '/' . $revision);
        if (!$directory->exists()) {
            return $nodes;
        }

        $sites = $this->getSites();

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

            $node->setRevision($revision);

            $nodes[$node->getId()] = $node;
        }

        // set the parent node instances and site revisions
        foreach ($nodes as $node) {
            if ($node->getType() === SiteNodeType::NAME) {
                $node->setRevisions($sites[$node->getId()]->getRevisions());
            }

            $parentId = $node->getParentNodeId();
            if (!$parentId) {
                // site node
                $node->setWidgetIdOffset($this->widgetIdOffset);

                continue;
            }

            if (isset($nodes[$parentId])) {
                $node->setParentNode($nodes[$parentId]);
            } else {
                $rootId = $node->getRootNodeId();
                if (isset($nodes[$rootId])) {
                    $node->setParentNode($nodes[$rootId]);
                }
            }
        }

        return $nodes;
    }

    /**
     * Reads all the nodes from the trash
     * @param string $siteId Id of the site
     * @return array Array with Node objects
     */
    protected function readTrash($siteId) {
        $trash = array();

        $directory = $this->path->getChild($siteId . '/' . $this->trashName);
        if (!$directory->exists()) {
            return $trash;
        }

        $files = $directory->read();
        foreach ($files as $file) {
            if ($file->isDirectory() || $file->getExtension() != 'ini') {
                continue;
            }

            try {
                $ini = $file->read();
                $node = $this->getNodeFromIni($ini);
                $node->setRevision($this->trashName);
            } catch (Exception $exception) {
                throw new CmsException('Could not parse the INI configuration from ' . $file->getName(), 0, $exception);
            }

            $id = str_replace('.ini', '', $file->getName());
            list($date, $nodeId) = explode('-', $id);

            $trashNode = new TrashNode($id, $node, $date);

            $trash[$trashNode->getId()] = $trashNode;
        }

        return $trash;
    }

    /**
     * Writes a node into the data source
     * @param \ride\library\cms\node\Node $node
     * @return null
     */
    protected function writeNode(Node $node) {
        $contents = $this->getIniFromNode($node);

        $nodeFile = $this->getNodeFile($node);

        $nodeFile->getParent()->create();
        $nodeFile->write($contents);
    }

    /**
     * Handles the expired routes
     * @param \ride\library\system\file\File $nodeFile
     * @param \ride\library\cms\node\Node $node
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
     * Deletes a node
     * @param \ride\library\cms\node\Node $node
     * @return null
     */
    protected function deleteNode(Node $node) {
        $siteId = $node->getRootNodeId();
        $revision = $node->getRevision();
        $nodeId = $node->getId();

        if ($siteId == $nodeId) {
            $nodeFile = $this->path->getChild($siteId);

            $trashFile = null;
        } else {
            $nodeFile = $this->getNodeFile($node);

            $trashFile = $this->path->getChild($siteId . '/' . $this->trashName . '/'  . time() . '-' . $nodeFile->getName());
        }

        if ($nodeFile->exists()) {
            if ($trashFile) {
                $nodeFile->copy($trashFile);
            }

            $nodeFile->delete();
        }
    }

    /**
     * Restores a node
     * @param string $siteId
     * @param string $revision
     * @param \ride\library\cms\node\Node $node
     * @param string $newParent
     * @return null
     */
    protected function restoreTrashNode($siteId, $revision, TrashNode $trashNode, $newParent = null) {
        $node = $trashNode->getNode();

        // resolve the parent
        $parent = $node->getParentNodeId();
        if ($newParent && $parent != $newParent) {
            $isNewParent = true;

            $parent = $this->getNode($siteId, $revision, $newParent);
        } else {
            $isNewParent = false;

            try {
                $parent = $this->getNode($siteId, $revision, $parent);
            } catch (CmsException $exception) {
                $parent = $this->getNode($siteId, $revision, $siteId);
            }
        }

        $node->setRevision($revision);
        $node->setParentNode($parent);
        $node->setId(null);

        // check for order conflicts
        $orderIndex = 0;

        $siblings = $this->getChildren($siteId, $revision, $parent->getPath(), 0);
        if ($isNewParent) {
            foreach ($siblings as $siblingNode) {
                $siblingOrderIndex = $siblingNode->getOrderIndex();
                if ($siblingOrderIndex > $orderIndex) {
                    $orderIndex = $siblingOrderIndex;
                }
            }

            $node->setOrderIndex($orderIndex + 1);
        } else {
            $nodeOrderIndex = $node->getOrderIndex();

            foreach ($siblings as $siblingNodeId => $siblingNode) {
                $orderIndex++;

                $siblingOrderIndex = $siblingNode->getOrderIndex();
                $isBefore = $siblingOrderIndex < $nodeOrderIndex;

                if ($isBefore && $siblingOrderIndex == $orderIndex) {
                    continue;
                } elseif ($siblingOrderIndex == $siblingOrderIndex) {
                    $orderIndex++;
                }

                $siblingNode->setOrderIndex($orderIndex);
                $changedNodes[] = $siblingNode;
            }
        }

        // save changes
        $changedNodes[] = $node;

        foreach ($changedNodes as $changedNode) {
            $this->setNode($changedNode);
        }

        // remove node from trash
        $trashFile = $this->path->getChild($siteId . '/' . $this->trashName . '/'  . $trashNode->getId() . '.ini');
        if ($trashFile->exists()) {
            $trashFile->delete();
        }
    }

    /**
     * Gets the file for the node
     * @param \ride\library\cms\node\Node $node
     * @return \ride\library\system\file\File
     */
    protected function getNodeFile(Node $node) {
        $rootNodeId = $node->getRootNodeId();
        $revision = $node->getRevision();
        $nodeId = $node->getId();

        return $this->path->getChild($rootNodeId . '/' . $revision . '/' . $nodeId . '.ini');
    }

    /**
     * Gets a node from a INI string
     * @param string $ini
     * @return \ride\library\cms\node\Node
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
     * Gets the ini string of a node
     * @param \ride\library\cms\node\Node $node
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
     * Publishes a node to the provided revision
     * @param \ride\library\cms\node\Node $node
     * @param string $revision
     * @param boolean $recursive Flag to see if the node's children should be
     * published as well
     * @return null
     */
    public function publish(Node $node, $revision, $recursive) {
        if ($node->getRevision() == $revision) {
            return;
        }

        $site = $node->getRootNode();
        $siteId = $site->getId();
        $nodeId = $node->getId();

        $publishDirectory = $this->path->getChild($siteId . '/' . $revision);

        if (!$publishDirectory->exists()) {
            // first publish
            $sourceDirectory = $this->path->getChild($siteId . '/' . $node->getRevision());
            $sourceDirectory->copy($publishDirectory);

            return;
        }

        // publish revsion exists, archive the revision before publishing
        $archiveDirectory = $this->path->getChild($siteId . '/' . $this->archiveName . '/' . date('YmdHis'));
        $publishDirectory->copy($archiveDirectory);

        // publish node
        $this->publishNode($site, $node, $revision, $publishDirectory, false);

        if ($recursive) {
            // publish recursive nodes
            $oldNodes = $this->getNodesByPath($siteId, $revision, $node->getPath());

            $nodes = $this->getNodesByPath($siteId, $site->getRevision(), $node->getPath());
            foreach ($nodes as $nodeId => $node) {
                $this->publishNode($site, $node, $revision, $publishDirectory, true);

                if (isset($oldNodes[$nodeId])) {
                    unset($oldNodes[$nodeId]);
                }
            }

            // deleted the removed nodes
            foreach ($oldNodes as $oldNodeId => $oldNode) {
                $oldNodeFile = $this->getNodeFile($oldNode);
                $oldNodeFile->delete();
            }

            $this->expiredRouteModel->removeExpiredRoutesByNode($siteId, array_keys($oldNodes));
        }
    }

    /**
     * Perform the actual publishing of a single node
     * @param \ride\library\cms\node\SiteNode $site
     * @param \ride\library\cms\node\Node $node
     * @param string $revision
     * @param \ride\library\system\file\File $publishDirectory
     * @param boolean $isRecursive Flag to see if this publishing is part of a
     * recursive publish action
     * @return null
     */
    protected function publishNode(SiteNode $site, Node $node, $revision, File $publishDirectory, $isRecursive) {
        // initialize needed variables
        $siteId = $site->getId();
        $nodeId = $node->getId();

        $changedNodes = array();

        try {
            $publishSite = $this->getSite($siteId, $revision);
        } catch (NodeNotFoundException $exception) {
            $publishSite = null;
        }

        // handle expired routes
        try {
            $oldNode = $this->getNode($siteId, $revision, $nodeId);

            // check for expired routes
            $oldRoutes = $oldNode->getRoutes();
            $newRoutes = $node->getRoutes();

            if ($oldRoutes !== $newRoutes) {
                foreach ($oldRoutes as $locale => $route) {
                    if (isset($newRoutes[$locale]) && $route === $newRoutes[$locale]) {
                        continue;
                    }

                    $this->expiredRouteModel->addExpiredRoute($siteId, $nodeId, $locale, $route, $site->getBaseUrl($locale));
                }
            }

            // check for order conflicts
            $nodeOrderIndex = $node->getOrderIndex();
            $nodeParent = $node->getParent();
            if (!$isRecursive && ($nodeOrderIndex != $oldNode->getOrderIndex() || $nodeParent != $oldNode->getParent())) {
                $orderIndex = 0;

                $parentNodes = $this->getChildren($siteId, $revision, $nodeParent, 0);
                foreach ($parentNodes as $parentNodeId => $parentNode) {
                    $orderIndex++;

                    $parentOrderIndex = $parentNode->getOrderIndex();
                    $isBefore = $parentOrderIndex < $nodeOrderIndex;

                    if ($isBefore && $parentOrderIndex == $orderIndex) {
                        continue;
                    } elseif ($nodeOrderIndex == $parentOrderIndex && $nodeId != $parentNodeId) {
                        $orderIndex++;

                        $parentNode->setOrderIndex($orderIndex);
                        $changedNodes[] = $parentNode;
                    } elseif ($nodeId == $parentNodeId) {
                        $orderIndex--;

                        continue;
                    } else {
                        $parentNode->setOrderIndex($orderIndex);
                        $changedNodes[] = $parentNode;
                    }
                }
            }
        } catch (NodeNotFoundException $exception) {

        }

        // check for new widgets
        if ($publishSite) {
            $isPublishSiteChanged = false;

            $usedWidgets = $node->getUsedWidgets();
            $availableWidgetsSite = $site->getAvailableWidgets();
            $availableWidgetsPublishSite = $publishSite->getAvailableWidgets();
            foreach ($usedWidgets as $widgetId) {
                if (!$widgetId || isset($availableWidgetsPublishSite[$widgetId]) || !isset($availableWidgetsSite[$widgetId])) {
                    continue;
                }

                $publishSite->set(Node::PROPERTY_WIDGET . '.' . $widgetId, $availableWidgetsSite[$widgetId], true);

                $isPublishSiteChanged = true;
            }

            if ($isPublishSiteChanged) {
                $changedNodes[] = $publishSite;
            }
        }

        // write the channged nodes
        foreach ($changedNodes as $changedNode) {
            $this->writeNode($changedNode);
        }

        // write the node file to the publish directory
        $nodeFile = $this->getNodeFile($node);
        $publishFile = $publishDirectory->getChild($nodeFile->getName());
        if ($nodeFile->exists()) {
            $nodeFile->copy($publishFile);
        } elseif ($publishFile->exists()) {
            $publishFile->delete();
        }
    }

}
