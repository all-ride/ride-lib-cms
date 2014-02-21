<?php

namespace ride\library\cms\node\validator;

use ride\library\cms\node\Node;
use ride\library\cms\node\NodeModel;

/**
 * Interface for a node validator
 */
interface NodeValidator {

    /**
     * Validates the node properties
     * @param ride\library\cms\node\Node $node Node to be validated
     * @param ride\library\cms\node\NodeModel $nodeModel Model of the
     * available nodes
     * @return null
     * @throws ride\library\validation\exception\ValidationException when a
     * property is not valid
     */
    public function validateNode(Node $node, NodeModel $nodeModel);

}