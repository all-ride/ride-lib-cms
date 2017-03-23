<?php

namespace ride\library\cms\node\type;

use ride\library\cms\node\HomeNode;

/**
 * Implementation of the home node type
 */
class HomeNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'home';

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode() {
        return new HomeNode();
    }

}
