<?php

namespace ride\library\cms\node;

use ride\library\cms\node\type\PageNodeType;

/**
 * Node implementation for a page
 */
class PageNode extends Node {

    /**
     * Constructs a new site node
     * @param string $type Name of the node type
     * @return null
     */
    public function __construct($type = null) {
        if (!$type) {
            $type = PageNodeType::NAME;
        }

        parent::__construct($type);

        $this->defaultInherit = false;
    }

}
