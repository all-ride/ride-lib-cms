<?php

namespace ride\library\cms\content\text\variable;

use ride\library\cms\exception\CmsException;
use ride\library\cms\node\exception\NodeNotFoundException;
use ride\library\cms\node\NodeModel;

/**
 * Implementation to parse node variables
 */
class NodeVariableParser extends AbstractVariableParser {

    /**
     * Name of the name variable
     * @var string
     */
    const VARIABLE_NAME = 'name';

    /**
     * Name of the url variable
     * @var string
     */
    const VARIABLE_URL = 'url';

    /**
     * Instance of the node model
     * @var \ride\library\cms\node\NodeModel
     */
    protected $nodeModel;

    /**
     * Constructs a new text parser
     * @param \ride\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @return null
     */
    public function __construct(NodeModel $nodeModel) {
        $this->nodeModel = $nodeModel;
    }

    /**
     * Parses the provided variable
     * @param string $variable Full variable
     * @param array $tokens Tokens of the variable, exploded on . (dot)
     * @return mixed Value of the variable if resolved, null otherwise
     */
    public function parseVariable($variable, array $tokens) {
        switch ($tokens[0]) {
            case 'node':
                if (count($tokens) < 3) {
                    return $matches[0];
                }

                try {
                    $node = $this->nodeModel->getNode($tokens[1], 0);
                } catch (NodeNotFoundException $exception) {
                    return $matches[0];
                }

                switch ($tokens[2]) {
                    case self::VARIABLE_URL:
                        return $this->textParser->getBaseUrl() . $node->getRoute(isset($tokens[3]) ? $tokens[3] : $this->textParser->getLocale());
                    case self::VARIABLE_NAME:
                        return $node->getName(isset($tokens[3]) ? $tokens[3] : $this->textParser->getLocale());
                }

                break;
            case 'site':
                if (count($tokens) < 2) {
                    return $matches[0];
                }

                switch ($tokens[1]) {
                    case self::VARIABLE_NAME:
                        return $this->textParser->getNode()->getRootNode()->getName(isset($tokens[2]) ? $tokens[2] : $this->textParser->getLocale());
                    case self::VARIABLE_URL:
                        return $this->textParser->getBaseUrl();
                }

                break;
        }

        return null;
    }

}
