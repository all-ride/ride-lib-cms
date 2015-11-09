<?php

namespace ride\library\cms\content\text\variable;

use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
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
        $node = null;

        switch ($tokens[0]) {
            case 'year':
                if (count($tokens) !== 1) {
                    return null;
                }

                return date('Y');
            case 'node':
                if (count($tokens) < 3) {
                    return null;
                }

                try {
                    $selfNode = $this->textParser->getNode();
                    $node = $this->nodeModel->getNode($selfNode->getRootNodeId(), $selfNode->getRevision(), $tokens[1]);
                } catch (NodeNotFoundException $exception) {
                    return null;
                }

                $locale = isset($tokens[3]) ? $tokens[3] : $this->textParser->getLocale();
                $variable = $tokens[2];

                break;
            case 'page':
                if (count($tokens) < 4) {
                    return null;
                }

                try {
                    $selfNode = $this->textParser->getNode();
                    $node = $this->nodeModel->getNode($tokens[1], $selfNode->getRevision(), $tokens[2]);
                } catch (NodeNotFoundException $exception) {
                    return null;
                }

                $locale = isset($tokens[4]) ? $tokens[4] : $this->textParser->getLocale();
                $variable = $tokens[3];

                break;
            case 'site':
                if (count($tokens) < 2) {
                    return null;
                }

                $node = $this->textParser->getNode()->getRootNode();
                $locale = isset($tokens[2]) ? $tokens[2] : $this->textParser->getLocale();
                $variable = $tokens[1];
        }

        if (!$node) {
            return null;
        }

        switch ($variable) {
            case self::VARIABLE_URL:
                return $node->getUrl($locale, $this->textParser->getSiteUrl());
            case self::VARIABLE_NAME:
                return $node->getName($locale);
            case self::VARIABLE_LINK:
                return '<a href="' . $node->getUrl($locale, $this->textParser->getSiteUrl()) . '">' . $node->getName($locale) . '</a>';
        }

        return null;
    }

}
