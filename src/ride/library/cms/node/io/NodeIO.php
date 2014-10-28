<?php

namespace ride\library\cms\node\io;

use ride\library\cms\node\Node;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\SiteNode;

/**
 * Interface for the node input/output implementation
 */
interface NodeIO {

    /**
     * Sets the instance of the node model
     * @param \ride\library\cms\node\NodeModel $nodeModel
     * @return null
     */
    public function setNodeModel(NodeModel $nodeModel);

    /**
     * Gets all the sites
     * @return array Array with the id of the site as key and the SiteNode as
     * value
     */
    public function getSites();

    /**
     * Gets a site by it's id
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\SiteNode
     */
    public function getSite($siteId, $revision, $children = false, $depth = false);

    /**
     * Gets a node
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $nodeId Id of the node
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\Node
     * @throws \ride\library\cms\node\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($siteId, $revision, $nodeId, $type = null, $children = false, $depth = false);

    /**
     * Gets the nodes of the provided path
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $path Path of the parent node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return array
     */
    public function getChildren($siteId, $revision, $path, $depth);

    /**
     * Reads all the nodes from the data source
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array Array with Node objects
     */
    public function getNodes($siteId, $revision);

    /**
     * Gets all the nodes of a certain type
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $type Name of the type
     * @return array
     */
    public function getNodesByType($siteId, $revision, $type);

    /**
     * Gets all the nodes for a specific path
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $path Materialized path for the nodes
     * @return array Array with Node instances
     */
    public function getNodesByPath($siteId, $revision, $path);

    /**
     * Writes a the nodes into the data source
     * @param \ride\library\cms\node\Node $node
     * @return null
     */
    public function setNode(Node $node);

    /**
     * Deletes a node from the data source
     * @param \ride\library\cms\node\Node $node
     * @param boolean $recursive Flag to see if child nodes should be deleted
     * @return null
     */
    public function removeNode(Node $node, $recursive = true);

    /**
     * Gets the trash of a site
     * @param string $siteId Id of the site
     * @return array Array with the id of the trash node as key and a TrashNode
     * instance as value
     */
    public function getTrashNodes($siteId);

    /**
     * Gets a node from the trash of a site
     * @param string $siteId Id of the site
     * @param string $trashNodeId Id of the trash node
     * @return \ride\library\cms\node\TrashNode
     * @throws \ride\library\cms\exception\NodeNotFoundException
     */
    public function getTrashNode($siteId, $trashNodeId);

    /**
     * Restores the provided node or array of nodes
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param \ride\library\cms\node\TrashNode|array $trashNode An instance of
     * TrashNode or an array of TrashNode instances
     * @param string $newParent Id of the new parent
     * @return null
     */
    public function restoreTrashNodes($siteId, $revision, $trashNodes, $newParent = null);

    /**
     * Publishes a site to the provided revision
     * @param \ride\library\cms\node\Node $node
     * @param string $revision
     * @param boolean $recursive Flag to see if the node's children should be
     * published as well
     * @return null
     */
    public function publish(Node $node, $revision, $recursive);

}
