<?php

namespace ride\library\cms\node\type;

/**
 * Interface for a node type
 */
interface NodeType {

    /**
     * Gets the machine name of this node type
     * @return string
     */
    public function getName();

    /**
     * Gets the callback for the frontend action
     * @return null|callback Null if the node type does not implement a
     * frontend, a callback for the action otherwise
     */
    public function getFrontendCallback();

    /**
     * Gets the default inherit value for a new node property
     * @return boolean
     */
    public function getDefaultInherit();

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode();

}