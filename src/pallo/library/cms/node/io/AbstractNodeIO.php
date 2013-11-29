<?php

namespace pallo\library\cms\node\io;

use pallo\library\cms\node\exception\NodeNotFoundException;
use pallo\library\cms\node\Node;
use pallo\library\cms\node\NodeModel;
use pallo\library\String;

/**
 * Abstract implementation for a node input/output
 */
abstract class AbstractNodeIO implements NodeIO {

    /**
     * Instance of the node model
     * @var pallo\library\cms\node\NodeModel
     */
    protected $nodeModel;

    /**
     * Array with the available nodes
     * @var array
     */
    protected $nodes;

    /**
     * Sets the instance of the node model
     * @param pallo\library\cms\node\NodeModel $nodeModel
     * @return null
     */
    public function setNodeModel(NodeModel $nodeModel) {
        $this->nodeModel = $nodeModel;
    }

    /**
     * Gets a node
     * @param string $nodeId Id of the node
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return pallo\library\cms\node\Node
     * @throws pallo\library\cms\node\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($nodeId, $type = null, $children = false, $depth = false) {
        $this->getNodes();

        if (!isset($this->nodes[$nodeId])) {
            throw new NodeNotFoundException($nodeId);
        }

        $node = $this->nodes[$nodeId];

        if ($type !== null && $node->getType() != $type) {
            throw new NodeNotFoundException($nodeId);
        }

        if ($children) {
            $node->setChildren($this->getChildren($node->getPath(), $depth));
        }

        return $node;
    }

    /**
     * Gets the children of the provided node
     * @param string $path Path of the parent node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return array
     */
    public function getChildren($path, $depth) {
        $children = array();
        $order = array();

        foreach ($this->nodes as $child) {
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
                $node->setChildren($this->getChildren($node->getPath(), $depth));
            }

            $children[$node->getId()] = $node;
        }

        return $children;
    }

    /**
     * Gets all the nodes
     * @return array
     */
    public function getNodes() {
        if ($this->nodes === null) {
            $this->readNodes();
        }

        return $this->nodes;
    }

    /**
     * Gets all the nodes of a certain type
     * @param string $type Name of the type
     * @return array
     */
    public function getNodesByType($type) {
        $this->getNodes();

        $nodes = array();

        foreach ($this->nodes as $node) {
            if ($node->getType() != $type) {
                continue;
            }

            $nodes[$node->getId()] = $node;
        }

        return $nodes;
    }

    /**
     * Gets all the nodes for a specific path
     * @param string $path Materialized path for the nodes
     * @return array Array with Node instances
     */
    public function getNodesByPath($path) {
        $this->getNodes();

        $nodes = array();

        foreach ($this->nodes as $node) {
            $parent = $node->getParent();
            if ($parent == $path || strpos($parent, $path . Node::PATH_SEPARATOR) === 0) {
                $nodes[$node->getId()] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Writes a the nodes into the data source
     * @param pallo\library\cms\node\Node $node
     * @return null
     */
    public function setNode(Node $node) {
        $id = $node->getId();
        if (!$id) {
            $id = $this->getNewNodeId($node);

            $node->setId($id);
        }

        $this->writeNode($node);

        if ($this->nodes === null) {
            $this->readNodes();
        } else {
            $this->nodes[$id] = $node;
        }
    }

    /**
     * Gets a id for a new node
     * @param pallo\library\cms\node\Node $node Node in need of a id
     * @return string
     */
    protected function getNewNodeId(Node $node) {
        $this->getNodes();

        $baseId = new String($node->getName());
        $baseId = str_replace(array('.', '-', ' '), '', $baseId->safeString());

        $id = $baseId;
        $index = 1;

        while (isset($this->nodes[$id])) {
            $id = $baseId . $index;

            $index++;
        }

        return $id;
    }

    /**
     * Removes a node
     * @param pallo\library\cms\node\Node $node Node to delete
     * @param boolean $recursive Flag to see if child nodes should be deleted
     * @return
     */
    public function removeNode(Node $node, $recursive = true) {
        $children = $this->getNodesByPath($node->getPath());
        foreach ($children as $child) {
            if ($recursive) {
                $this->removeNode($child, true);

                continue;
            }

            $child->setParent($node->getParent());

            $this->setNode($child);
        }

        $this->deleteNode($node);

        unset($this->nodes[$node->getId()]);
    }

    /**
     * Reads all the nodes from the data source into the $nodes variable
     * @return null
     */
    abstract protected function readNodes();

    /**
     * Writes the provided node to the data source
     * @param pallo\library\cms\node\Node $node Node to write
     * @return null
     */
    abstract protected function writeNode(Node $node);

    /**
     * Deletes the provided node to the data source
     * @param pallo\library\cms\node\Node $node Node to delete
     * @return null
     */
    abstract protected function deleteNode(Node $node);

}