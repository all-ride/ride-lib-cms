<?php

namespace pallo\library\cms\node\type;

use pallo\library\cms\node\RedirectNode;

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
     * @return pallo\library\cms\node\Node
     */
    public function createNode() {
        return new RedirectNode();
    }

}