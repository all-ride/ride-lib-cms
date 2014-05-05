<?php

namespace ride\library\cms\content\text\variable;

use ride\library\cms\content\text\TextParser;

/**
 * Interface to parse variables into their values
 */
abstract class AbstractVariableParser implements VariableParser {

    /**
     * Instance of the text parser
     * @var \ride\library\cms\content\text\TextParser
     */
    protected $textParser;

    /**
     * Sets the instance of the text parser which is holding this parser
     * @param \ride\library\cms\content\text\TextParser $textParser
     * @return null
     */
    public function setTextParser(TextParser $textParser) {
        $this->textParser = $textParser;
    }

}
