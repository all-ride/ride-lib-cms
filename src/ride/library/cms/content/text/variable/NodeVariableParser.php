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
     * Name of the link variable
     * @var string
     */
    const VARIABLE_LINK = 'link';

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
     * @return mixed Value of the variable if resolved, null otherwise
     */
    public function parseVariable($variable) {
        $tokens = explode('.', $variable);

        switch ($tokens[0]) {
            case 'node':
                if (count($tokens) < 3) {
                    return null;
                }

                try {
                    $node = $this->nodeModel->getNode($tokens[1], 0);
                } catch (NodeNotFoundException $exception) {
                    return null;
                }

                $locale = isset($tokens[3]) ? $tokens[3] : $this->textParser->getLocale();

                switch ($tokens[2]) {
                    case self::VARIABLE_URL:
                        return $this->textParser->getBaseUrl() . $node->getRoute($locale);
                    case self::VARIABLE_NAME:
                        return $node->getName($locale);
                    case self::VARIABLE_LINK:
                        return '<a href="' . $this->textParser->getBaseUrl() . $node->getRoute($locale) . '">' . $node->getName($locale) . '</a>';
                }

                break;
            case 'site':
                if (count($tokens) < 2) {
                    return null;
                }

                $locale = isset($tokens[2]) ? $tokens[2] : $this->textParser->getLocale();

                switch ($tokens[1]) {
                    case self::VARIABLE_NAME:
                        return $this->textParser->getNode()->getRootNode()->getName($locale);
                    case self::VARIABLE_URL:
                        return $this->textParser->getBaseUrl();
                    case self::VARIABLE_LINK:
                        return '<a href="' . $this->textParser->getBaseUrl() . '">' . $this->textParser->getNode()->getRootNode()->getName($locale) . '</a>';
                }

                break;
        }

        return null;
    }

}
