<?php

namespace ride\library\cms\node\type;

use ride\library\cms\node\ReferenceNode;

/**
 * Implementation of the reference node type
 */
class ReferenceNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'reference';

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode() {
        return new ReferenceNode();
    }

}
