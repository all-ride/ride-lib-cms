<?php

namespace pallo\library\cms\node\type;

use pallo\library\cms\node\PageNode;

/**
 * Implementation of the page node type
 */
class PageNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'page';

    /**
     * Gets the default inherit value for a new node property
     * @return boolean
     */
    public function getDefaultInherit() {
        return false;
    }

    /**
     * Creates a new node of this type
     * @return pallo\library\cms\node\Node
     */
    public function createNode() {
        return new PageNode();
    }

}