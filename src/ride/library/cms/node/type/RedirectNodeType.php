<?php

namespace ride\library\cms\node\type;

use ride\library\cms\node\RedirectNode;

/**
 * Implementation of the page node type
 */
class RedirectNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'redirect';

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode() {
        return new RedirectNode();
    }

}