<?php

namespace ride\library\cms\node\io;

use ride\library\cms\expired\ExpiredRouteModel;
use ride\library\cms\exception\CmsException;
use ride\library\cms\node\NodeProperty;
use ride\library\cms\node\Node;
use ride\library\cms\node\SiteNode;
use ride\library\system\file\File;

/**
 * INI implementation of the NodeIO
 */
abstract class AbstractFileNodeIO extends AbstractNodeIO {

    /**
     * Path for the node files
     * @var \ride\library\system\file\File
     */
    protected $path;

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
     * @param \ride\library\cms\expired\ExpiredRouteModel $expiredRouteModel
     * Instance of the expired route model
     * @return null
     */
    public function __construct(File $path, ExpiredRouteModel $expiredRouteModel) {
        $this->setExpiredRouteModel($expiredRouteModel);

        $this->path = $path;
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
            if ($siteDirectory->isHidden() || !$siteDirectory->isDirectory()) {
                continue;
            }

            $site = $this->readSite($siteDirectory, $defaultRevision);

            $sites[$site->getId()] = $site;
        }

        return $sites;
    }

    /**
     * Reads the site and it's revisions from the provided directory
     * @param \ride\library\system\file\File $siteDirectory Directory of the site
     * @param string $defaultRevision Default revision for the site
     * @return \ride\library\cms\node\SiteNode
     * @throws \ride\library\cms\exception\CmsException when the site could not
     * be read
     */
    abstract protected function readSite(File $siteDirectory, $defaultRevision);

    /**
     * Reads the site revisions from the provided site directory
     * @param \ride\library\system\file\File $siteDirectory Directory of the site
     * @param string $defaultRevision Default revision for the site
     * @param string $revision Revision to load
     * @return array Array with the name of the revision as key and value
     * @throws \ride\library\cms\exception\CmsException when the site directory
     * could not be read
     */
    protected function readSiteRevisions(File $siteDirectory, $defaultRevision, &$revision = null) {
        $revisions = array();

        $files = $siteDirectory->read();
        foreach ($files as $file) {
            $revision = $file->getName(true);
            if ($revision === $this->archiveName || $revision === $this->trashName || !$file->isDirectory()) {
                continue;
            }

            $revisions[$revision] = $revision;
        }

        if (!$revisions) {
            throw new CmsException('No valid site in ' . $siteDirectory->getAbsolutePath());
        }

        if (isset($revisions[$defaultRevision])) {
            $revision = $defaultRevision;
        } else {
            $revision = reset($revisions);
        }

        return $revisions;
    }

    /**
     * Gets a node from a array
     * @param array $array Array with the properties of the node
     * @return \ride\library\cms\node\Node
     */
    protected function getNodeFromArray(array $array) {
        if (!isset($array[self::PROPERTY_ID])) {
            throw new CmsException('No id provided for the node');
        }
        $id = $array[self::PROPERTY_ID];
        unset($array[self::PROPERTY_ID]);

        if (!isset($array[self::PROPERTY_TYPE])) {
            throw new CmsException('No type provided for node ' . $id);
        }
        $type = $array[self::PROPERTY_TYPE];
        unset($array[self::PROPERTY_TYPE]);

        if (!isset($array[self::PROPERTY_PARENT])) {
            $parent = null;
            $order = null;
        } else {
            $parent = $array[self::PROPERTY_PARENT];
            unset($array[self::PROPERTY_PARENT]);

            if (isset($array[self::PROPERTY_ORDER])) {
                $orderIndex = $array[self::PROPERTY_ORDER];
                unset($array[self::PROPERTY_ORDER]);
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
        foreach ($array as $key => $value) {
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
     * Gets an array of a node with all it's properties
     * @param \ride\library\cms\node\Node $node
     * @return array
     */
    protected function getArrayFromNode(Node $node) {
        $array = array();
        $array[self::PROPERTY_TYPE] = new NodeProperty(self::PROPERTY_TYPE, $node->getType());
        $array[self::PROPERTY_ID] = new NodeProperty(self::PROPERTY_ID, $node->getId());
        $array[self::PROPERTY_PARENT] = new NodeProperty(self::PROPERTY_PARENT, $node->getParent());
        $array[self::PROPERTY_ORDER] = new NodeProperty(self::PROPERTY_ORDER, $node->getOrderIndex());
        $array += $node->getProperties();

        return $array;
    }

}
