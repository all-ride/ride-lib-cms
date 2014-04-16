<?php

namespace ride\library\cms\content\text;

use ride\library\cms\content\text\variable\VariableParser;

/**
 * Text parser to replace variable placeholders with their values
 */
class VariablesTextParser extends AbstractTextParser {

    /**
     * Regular expression for a node variable
     * @var string
     */
    const REGEX_VARIABLE = '/%(([a-zA-Z0-9]*)\\.)*([a-zA-Z0-9]*)%/';

    /**
     * Variable parsers for the chain
     * @var array
     */
    protected $variableParsers;

    /**
     * Adds a variable parser to the chain
     * @param \ride\library\cms\content\text\variable\VariableParser
     * $variableParser Instance to add
     * @return null
     */
    public function addVariableParser(VariableParser $variableParser) {
        $variableParser->setTextParser($this);

        $this->variableParsers[] = $variableParser;
    }

    /**
     * Removes a variable parser from the chain
     * @param \ride\library\cms\content\text\variable\VariableParser
     * $variableParser Instance to be removed
     * @return null
     */
    public function removeVariableParser(VariableParser $variableParser) {
        foreach ($this->variableParsers as $index => $loopVariableParser) {
            if ($variableParser === $loopVariableParser) {
                unset($this->variableParsers[$index]);
            }
        }
    }

    /**
     * Parses a text for display
     * @param string $text Text to parse
     * @return string Parsed text
     */
    public function parseText($text) {
        if (!$text || !is_string($text)) {
            return $text;
        }

        return preg_replace_callback(self::REGEX_VARIABLE, array($this, 'getParsedVariable'), $text);
    }

    /**
     * Gets the value of the provided variable
     * @param array $matches The matches of the variable regular expression
     * @return string The value of the provided variable
     * @throws \ride\library\cms\exception\CmsException when an unsupported
     * variable is provided
     */
    protected function getParsedVariable(array $matches) {
        $variable = substr($matches[0], 1, -1);
        $tokens = explode('.', $variable);

        foreach ($this->variableParsers as $variableParser) {
            $value = $variableParser->parseVariable($variable, $tokens);
            if ($value !== null) {
                return $value;
            }
        }

        return $matches[0];
    }

}
