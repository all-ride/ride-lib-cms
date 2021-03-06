<?php

namespace ride\library\cms\node\type;

use ride\library\cms\node\Node;

/**
 * Abstract node type
 */
abstract class AbstractNodeType implements NodeType {

    /**
     * Gets the machine name of this node type
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Gets the callback for the frontend route
     * @return string|array|\ride\library\Callback
     */
    public function getFrontendCallback() {
        return null;
    }

    /**
     * Gets the default inherit value for a new node property
     * @return boolean
     */
    public function getDefaultInherit() {
    	return true;
    }

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode() {
        $node = new Node($this->getName());
        $node->setDefaultInherit($this->getDefaultInherit());

        return $node;
    }

}