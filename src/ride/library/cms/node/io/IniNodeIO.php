<?php

namespace ride\library\cms\node\io;

use ride\library\cms\expired\ExpiredRouteModel;
use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\type\ReferenceNodeType;
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
class IniNodeIO extends AbstractFileNodeIO {

    /**
     * Instance of the config helper
     * @var \ride\library\config\ConfigHelper
     */
    protected $configHelper;

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
        parent::__construct($path, $expiredRouteModel);

        $this->configHelper = $configHelper;

        $this->archiveName = '_archive';
        $this->trashName = '_trash';
    }

    /**
     * Reads the site and it's revisions from the provided directory
     * @param \ride\library\system\file\File $siteDirectory Directory of the site
     * @param string $defaultRevision Default revision of the site
     * @return \ride\library\cms\node\SiteNode
     * @throws \ride\library\cms\exception\CmsException when the site could not
     * be read
     */
    protected function readSite(File $siteDirectory, $defaultRevision) {
        $revision = null;
        $revisions = $this->readSiteRevisions($siteDirectory, $defaultRevision, $revision);

        $siteFile = $siteDirectory->getChild($revision . '/' . $siteDirectory->getName() . '.ini');
        if ($siteFile->exists()) {
            try {
                $ini = $siteFile->read();
                $site = $this->getNodeFromIni($ini);
            } catch (Exception $exception) {
                throw new CmsException('Could not parse the INI configuration from ' . $siteFile->getName(), 0, $exception);
            }
        } else {
            throw new CmsException('No valid site in ' . $siteFile->getAbsolutePath());
        }

        $site->setRevisions($revisions);
        $site->setRevision($revision);

        return $site;
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
        $dateModified = 0;

        $files = $directory->read();
        foreach ($files as $file) {
            if ($file->isDirectory() || $file->getExtension() != 'ini') {
                continue;
            }

            try {
                $ini = $file->read();
                $node = $this->getNodeFromIni($ini);
                $node->setDateModified($file->getModificationTime());

                $dateModified = max($dateModified, $node->getDateModified());
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
                $node->setDateModified($dateModified);
            } elseif ($node->getType() === ReferenceNodeType::NAME) {
                $referenceNodeId = $node->getReferenceNode();
                if (isset($nodes[$referenceNodeId])) {
                    $node->setNode($nodes[$referenceNodeId]);
                }
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

        // touch the site file to update the date modified
        $siteFile = $this->getNodeFile($node->getRootNode());
        if ($siteFile->exists()) {
            $siteFile->touch();
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
        $parsedIni = @parse_ini_string($ini, true);
        if ($parsedIni === false) {
            throw new CmsException('Could not parse ini: ' . $ini);
        }

        $array = $this->configHelper->flattenConfig($parsedIni);

        return $this->getNodeFromArray($array);
    }

    /**
     * Gets the ini string of a node
     * @param \ride\library\cms\node\Node $node
     * @return string
     */
    protected function getIniFromNode(Node $node) {
        $ini = '';

        $properties = $this->getArrayFromNode($node);
        foreach ($properties as $property) {
            $ini .= $property->getIniString() . "\n";
        }

        return $ini;
    }

    /**
     * Publishes a site to the provided revision
     * @param \ride\library\cms\node\Node $node
     * @param string $revision
     * @param boolean $recursive Flag to see if the node's children should be
     * published as well
     * @return array|null Nodes have been deleted
     */
    public function publish(Node $node, $revision, $recursive) {
        $deletedNodes = array();

        if ($node->getRevision() == $revision) {
            return $deletedNodes;
        }

        $site = $node->getRootNode();
        $siteId = $site->getId();
        $nodeId = $node->getId();

        $publishDirectory = $this->path->getChild($siteId . '/' . $revision);

        if (!$publishDirectory->exists()) {
            // first publish
            $sourceDirectory = $this->path->getChild($siteId . '/' . $node->getRevision());
            $sourceDirectory->copy($publishDirectory);

            return $deletedNodes;
        }

        // publish revision exists, archive the revision before publishing
        $archiveDirectory = $this->path->getChild($siteId . '/' . $this->archiveName . '/' . date('YmdHis'));
        $publishDirectory->copy($archiveDirectory);

        // publish node
        $deletedNode = $this->publishNode($site, $node, $revision, $publishDirectory, false);
        if ($deletedNode) {
            $deletedNodes[$deletedNode->getId()] = $deletedNode;
        }

        if (!$recursive) {
            return $deletedNodes;
        }

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

            $deletedNodes[$oldNodeId] = $oldNode;
        }

        $this->expiredRouteModel->removeExpiredRoutesByNode($siteId, array_keys($oldNodes));

        return $deletedNodes;
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

        // process and merge the necessairy nodes
        try {
            $oldNode = $this->getNode($siteId, $revision, $nodeId);

            // check for expired routes
            $oldRoutes = $oldNode->getRoutes();
            $newRoutes = $node->getRoutes();

            if ($oldRoutes && $oldRoutes !== $newRoutes) {
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
            // new node in the revision
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

        // write the changed nodes
        foreach ($changedNodes as $changedNode) {
            $this->writeNode($changedNode);
        }

        // write the node file to the publish directory
        $nodeFile = $this->getNodeFile($node);

        $publishFile = $publishDirectory->getChild($nodeFile->getName());
        if ($nodeFile->exists()) {
            // node has been created or updated
            $nodeFile->copy($publishFile);

            return null;
        } elseif ($publishFile->exists()) {
            // node has been deleted
            $publishFile->delete();

            return $node;
        }
    }

}
