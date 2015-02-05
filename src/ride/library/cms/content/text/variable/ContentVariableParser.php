<?php

namespace ride\library\cms\content\text\variable;

use ride\library\cms\content\ContentFacade;

/**
 * Implementation to parse node variables
 */
class ContentVariableParser extends AbstractVariableParser {

    /**
     * Name of the title variable
     * @var string
     */
    const VARIABLE_TITLE = 'title';

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
     * Instance of the content facade
     * @var \ride\library\cms\content\ContentFacade
     */
    protected $contentFacade;

    /**
     * Constructs a new text parser
     * @param \ride\library\cms\content\ContentFacade $contentFacade
     * @return null
     */
    public function __construct(ContentFacade $contentFacade) {
        $this->contentFacade = $contentFacade;
    }

    /**
     * Parses the provided variable
     * @param string $variable Full variable
     * @return mixed Value of the variable if resolved, null otherwise
     */
    public function parseVariable($variable) {
        $tokens = explode('.', $variable);
        if (count($tokens) != 4) {
            return null;
        }

        switch ($tokens[0]) {
            case 'content':
                $node = $this->textParser->getNode();
                $locale = $this->textParser->getLocale();

                $contentMapper = $this->contentFacade->getContentMapper($tokens[1]);
                $content = $contentMapper->getContent($node->getRootNodeId(), $locale, $tokens[2]);
                if (!$content) {
                    return null;
                }

                switch ($tokens[3]) {
                    case self::VARIABLE_URL:
                        return $content->url;
                    case self::VARIABLE_NAME:
                        return $content->title;
                    case self::VARIABLE_LINK:
                        return '<a href="' . $content->url . '">' . $content->title . '</a>';
                }

                break;
        }

        return null;
    }

}
