<?php

namespace ride\library\cms\node\type;

use ride\library\cms\exception\CmsException;

/**
 * Manager of the node types
 */
class NodeTypeManager {

    /**
     * Array with NodeType objects as value and their name as key
     * @var array
     */
    protected $nodeTypes;

    /**
     * Construct this manager
     * @return null
     */
    public function __construct() {
        $this->nodeTypes = array();
    }

    /**
     * Adds a node type
     * @param NodeType $nodeType
     * @return null
     */
    public function addNodeType(NodeType $nodeType) {
        $this->nodeTypes[$nodeType->getName()] = $nodeType;
    }

    /**
     * Checks whether a node type is registered
     * @param string $name name of the node type
     * @return boolean true if the node type is registered, false otherwise
     */
    public function hasNodeType($name) {
        return isset($this->nodeTypes[$name]);
    }

    /**
     * Gets the implementation of a node type
     * @param string $name
     * @return NodeType
     * @throws ride\library\cms\exception\CmsException when the node type is
     * not added to this manager
     */
    public function getNodeType($name) {
        if (!$this->hasNodeType($name)) {
            throw new CmsException('Could not get node type: ' . $name . ' is not added to this manager');
        }

        return $this->nodeTypes[$name];
    }

    /**
     * Gets the available node types
     * @return array Array with the name of the type as key and the
     * implementation as value
     */
    public function getNodeTypes() {
        return $this->nodeTypes;
    }

    /**
     * Removes a node type
     * @param string $name Name of the node type
     * @return null
     * @throws ride\library\cms\exception\CmsException when the node type is
     * not added to this manager
     */
    public function removeNodeType($name) {
        if (!$this->hasNodeType($name)) {
            throw new CmsException('Could not remove node type: ' . $name . ' is not added to this manager');
        }

        unset($this->nodeTypes[$name]);
    }

}