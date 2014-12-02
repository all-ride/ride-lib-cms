<?php

namespace ride\library\cms\node\io;

use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\type\SiteNodeType;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\TrashNode;
use ride\library\StringHelper;

/**
 * Abstract implementation for a node input/output
 */
abstract class AbstractNodeIO implements NodeIO {

    /**
     * Instance of the node model
     * @var \ride\library\cms\node\NodeModel
     */
    protected $nodeModel;

    /**
     * Array with the available sites
     * @var array
     */
    protected $sites;

    /**
     * Array with the available nodes
     * @var array
     */
    protected $nodes;

    /**
     * Array with the loaded trash
     * @var array
     */
    protected $trash;

    /**
     * Sets the instance of the node model
     * @param \ride\library\cms\node\NodeModel $nodeModel
     * @return null
     */
    public function setNodeModel(NodeModel $nodeModel) {
        $this->nodeModel = $nodeModel;
    }

    /**
     * Gets all the sites
     * @return array Array with the id of the site as key and the SiteNode as
     * value
     */
    public function getSites() {
        if ($this->sites === null) {
            $this->sites = $this->readSites();
        }

        return $this->sites;
    }

    /**
     * Gets a site by it's id
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\SiteNode|null
     */
    public function getSite($siteId, $revision, $children = false, $depth = false) {
        return $this->getNode($siteId, $revision, $siteId, SiteNodeType::NAME, $children, $depth);
    }

    /**
     * Gets a node
     * @param string $siteId Id of the site
     * @param string $nodeId Id of the node
     * @param string $revision Name of the revision
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\Node
     * @throws \ride\library\cms\node\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($siteId, $revision, $nodeId, $type = null, $children = false, $depth = false) {
        if (!isset($this->nodes[$siteId][$revision])) {
            $this->nodes[$siteId][$revision] = $this->readNodes($siteId, $revision);
        }

        if (!isset($this->nodes[$siteId][$revision][$nodeId])) {
            throw new NodeNotFoundException();
        }

        $node = $this->nodes[$siteId][$revision][$nodeId];
        if ($type !== null && $node->getType() != $type) {
            throw new NodeNotFoundException();
        }

        if ($children) {
            $node->setChildren($this->getChildren($siteId, $node->getRevision(), $node->getPath(), $depth));
        }

        return $node;
    }

    /**
     * Gets the children of the provided node
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $path Path of the parent node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return array
     */
    public function getChildren($siteId, $revision, $path, $depth) {
        $children = array();

        if (!isset($this->nodes[$siteId][$revision])) {
            $this->nodes[$siteId][$revision] = $this->readNodes($siteId, $revision);
        }

        $order = array();

        foreach ($this->nodes[$siteId][$revision] as $child) {
            if ($child->getParent() != $path) {
                continue;
            }

            $orderIndex = $child->getOrderIndex();
            if ($orderIndex) {
                $order[$orderIndex] = $child;
            } else {
                $order['o' . count($order)] = $child;
            }
        }

        ksort($order);

        if ($depth !== false) {
            $depth--;
        }

        foreach ($order as $node) {
            if ($depth === false || $depth) {
                $node->setChildren($this->getChildren($siteId, $revision, $node->getPath(), $depth));
            }

            $children[$node->getId()] = $node;
        }

        return $children;
    }

    /**
     * Gets all the nodes for a site
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array
     */
    public function getNodes($siteId, $revision) {
        if (!isset($this->nodes[$siteId][$revision])) {
            $this->nodes[$siteId][$revision] = $this->readNodes($siteId, $revision);
        }

        return $this->nodes[$siteId][$revision];
    }

    /**
     * Gets all the nodes of a certain type
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $type Name of the type
     * @return array
     */
    public function getNodesByType($siteId, $revision, $type) {
        $this->getNodes($siteId, $revision);

        $nodes = array();

        foreach ($this->nodes[$siteId][$revision] as $node) {
            if ($node->getType() != $type) {
                continue;
            }

            $nodes[$node->getId()] = $node;
        }

        return $nodes;
    }

    /**
     * Gets all the nodes for a specific path
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $path Materialized path for the nodes
     * @return array Array with Node instances
     */
    public function getNodesByPath($siteId, $revision, $path) {
        $this->getNodes($siteId, $revision);

        $nodes = array();

        foreach ($this->nodes[$siteId][$revision] as $node) {
            $parent = $node->getParent();
            if ($parent == $path || strpos($parent, $path . Node::PATH_SEPARATOR) === 0) {
                $nodes[$node->getId()] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Writes a the nodes into the data source
     * @param \ride\library\cms\node\Node $node
     * @return null
     */
    public function setNode(Node $node) {
        $id = $node->getId();
        if (!$id) {
            $id = $this->getNewNodeId($node);

            $node->setId($id);
        }

        $this->writeNode($node);

        if ($node->getType() == SiteNodeType::NAME) {
            $revision = $node->getRevision();

            $node->setRevisions(array($revision => $revision));

            $this->sites[$id] = $node;
        }

        if ($this->nodes === null) {
            $this->readNodes($node->getRootNodeId(), $node->getRevision());
        } else {
            $this->nodes[$node->getRootNodeId()][$node->getRevision()][$id] = $node;
        }
    }

    /**
     * Gets a id for a new node
     * @param \ride\library\cms\node\Node $node Node in need of a id
     * @return string
     */
    protected function getNewNodeId(Node $node) {
        $revision = $node->getRevision();

        $baseId = StringHelper::safeString($node->getName());
        $baseId = str_replace(array('.', '-', ' '), '', $baseId);

        $id = $baseId;
        $index = 1;

        try {
            $siteId = $node->getRootNodeId();

            $this->getNodes($siteId, $revision);
            while (isset($this->nodes[$siteId][$revision][$id])) {
                $id = $baseId . $index;

                $index++;
            }
        } catch (CmsException $exception) {
            // new site node
            $this->getSites();

            while (isset($this->sites[$id])) {
                $id = $baseId . $index;

                $index++;
            }
        }

        return $id;
    }

    /**
     * Removes a node
     * @param \ride\library\cms\node\Node $node Node to delete
     * @param boolean $recursive Flag to see if child nodes should be deleted
     * @return
     */
    public function removeNode(Node $node, $recursive = true) {
        $siteId = $node->getRootNodeId();
        $revision = $node->getRevision();

        $parent = $node->getParent();
        $path = $node->getPath();
        $orderIndex = $node->getOrderIndex();
        $baseOrderIndex = $orderIndex - 1;

        $changedNodes = array();

        // remove children or move the children the the parent's path
        $numChildren = 0;
        $children = $this->getNodesByPath($siteId, $revision, $path);
        foreach ($children as $child) {
            if ($recursive) {
                $this->removeNode($child, true);

                continue;
            }

            $childParent = $child->getParent();
            if ($childParent === $path) {
                $child->setOrderIndex($baseOrderIndex + $child->getOrderIndex());

                $numChildren++;
            }

            $child->setParent(str_replace($path, $parent, $childParent));

            $changedNodes[] = $child;
        }

        if (!$recursive) {
            // fix order index for nodes after the removed node
            $siblings = $this->getChildren($node->getRootNodeId(), $node->getRevision(), $parent, 0);
            foreach ($siblings as $sibling) {
                $siblingOrderIndex = $sibling->getOrderIndex();
                if ($siblingOrderIndex <= $orderIndex) {
                    continue;
                }

                $sibling->setOrderIndex($siblingOrderIndex + $numChildren - 1);

                $changedNodes[] = $sibling;
            }
        }

        // save and remove the necessairy nodes
        foreach ($changedNodes as $changedNode) {
            $this->setNode($changedNode);
        }

        $this->deleteNode($node);

        unset($this->nodes[$siteId][$revision][$node->getId()]);
    }

    /**
     * Gets the trash of a site
     * @param string $siteId Id of the site
     * @return array Array with the id of the trash node as key and a Node
     * instance as value
     */
    public function getTrashNodes($siteId) {
        if (!isset($this->trash[$siteId])) {
            $this->trash[$siteId] = $this->readTrash($siteId);
        }

        return $this->trash[$siteId];
    }

    /**
     * Gets a node from the trash of a site
     * @param string $siteId Id of the site
     * @param string $trashNodeId Id of the trash node, this consists of
     * timestamp and node id separated by a dash
     * @return \ride\library\cms\node\Node
     * @throws \ride\library\cms\exception\NodeNotFoundException
     */
    public function getTrashNode($siteId, $trashNodeId) {
        $this->getTrash($siteId);

        if (!isset($this->trash[$siteId][$trashNodeId])) {
            throw new NodeNotFoundException();
        }

        return $this->trash[$siteId][$trashNodeId];
    }

    /**
     * Restores the provided node or array of nodes
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param \ride\library\cms\node\Node|array $trashNodes Array of, or a
     * single TrashNode instance
     * @param string $newParent Id of the new parent node
     * @return null
     */
    public function restoreTrashNodes($siteId, $revision, $trashNodes, $newParent = null) {
        if (!is_array($trashNodes)) {
            $trashNodes = array($trashNodes);
        }

        $order = array();

        foreach ($trashNodes as $trashNode) {
            if (is_string($trashNode)) {
                $trashNode = $this->getTrashNode($siteId, $trashNode);
            } elseif (!$trashNode instanceof TrashNode) {
                throw new CmsException('Could not restore node: proivided value should be an instance of TrashNode');
            }

            $node = $trashNode->getNode();
            $orderIndex = $node->getPath() . ' - ' . $node->getOrderIndex();

            $order[$orderIndex] = $trashNode;
        }

        ksort($order);

        foreach ($order as $trashNode) {
            $this->restoreTrashNode($siteId, $revision, $trashNode, $newParent);
        }
    }

    /**
     * Reads all the sites from the data source
     * @return array Array with the id of the site as key and the SiteNode as
     * value
     */
    abstract protected function readSites();

    /**
     * Reads all the nodes from the data source
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array Array with the id of the node as key and the Node as value
     */
    abstract protected function readNodes($siteId, $revision);

    /**
     * Writes the provided node to the data source
     * @param \ride\library\cms\node\Node $node Node to write
     * @return null
     */
    abstract protected function writeNode(Node $node);

    /**
     * Deletes the provided node to the data source
     * @param \ride\library\cms\node\Node $node Node to delete
     * @return null
     */
    abstract protected function deleteNode(Node $node);

    /**
     * Restores a node
     */
    abstract protected function restoreTrashNode($siteId, $revision, TrashNode $trashNode, $newParent = null);

}
