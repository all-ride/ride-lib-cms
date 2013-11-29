<?php

namespace pallo\library\cms\node\type;

use pallo\library\cms\node\SiteNode;

/**
 * Implementation of the page node type
 */
class SiteNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'site';

    /**
     * Creates a new node of this type
     * @return pallo\library\cms\node\Node
     */
    public function createNode() {
        return new SiteNode();
    }

}