<?php

namespace pallo\library\cms\node\validator;

use pallo\library\cms\node\Node;
use pallo\library\cms\node\NodeModel;

/**
 * Interface for a node validator
 */
interface NodeValidator {

    /**
     * Validates the node properties
     * @param pallo\library\cms\node\Node $node Node to be validated
     * @param pallo\library\cms\node\NodeModel $nodeModel Model of the
     * available nodes
     * @return null
     * @throws pallo\library\validation\exception\ValidationException when a
     * property is not valid
     */
    public function validateNode(Node $node, NodeModel $nodeModel);

}