<?php

namespace ride\library\cms\node\io;

use ride\library\cms\node\Node;
use ride\library\cms\node\NodeModel;

/**
 * Interface for the node input/output implementation
 */
interface NodeIO {

    /**
     * Sets the instance of the node model
     * @param ride\library\cms\node\NodeModel $nodeModel
     * @return null
     */
    public function setNodeModel(NodeModel $nodeModel);

    /**
     * Gets a node
     * @param string $nodeId Id of the node
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return ride\library\cms\node\Node
     * @throws ride\library\cms\node\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($nodeId, $type = null, $children = false, $depth = false);

    /**
     * Gets the children of the provided node
     * @param string $path Path of the parent node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return array
     */
    public function getChildren($path, $depth);

    /**
     * Reads all the nodes from the data source
     * @return array Array with Node objects
     */
    public function getNodes();

    /**
     * Gets all the nodes of a certain type
     * @param string $type Name of the type
     * @return array
     */
    public function getNodesByType($type);

    /**
     * Gets all the nodes for a specific path
     * @param string $path Materialized path for the nodes
     * @return array Array with Node instances
     */
    public function getNodesByPath($path);

    /**
     * Writes a the nodes into the data source
     * @param ride\library\cms\node\Node $node
     * @return null
     */
    public function setNode(Node $node);

    /**
     * Deletes a node from the data source
     * @param ride\library\cms\node\Node $node
     * @param boolean $recursive Flag to see if child nodes should be deleted
     * @return null
     */
    public function removeNode(Node $node, $recursive = true);

}