<?php

namespace ride\library\cms\content\text\variable;

use ride\library\cms\content\text\TextParser;

/**
 * Interface to parse variables into their values
 */
interface VariableParser {

    /**
     * Sets the instance of the text parser which is holding this parser
     * @param \ride\library\cms\content\text\TextParser $textParser
     * @return null
     */
    public function setTextParser(TextParser $textParser);

    /**
     * Parses the provided variable
     * @param string $variable Full variable
     * @param array $tokens Tokens of the variable, exploded on . (dot)
     * @return mixed Value of the variable if resolved, null otherwise
     */
    public function parseVariable($variable, array $tokens);

}
