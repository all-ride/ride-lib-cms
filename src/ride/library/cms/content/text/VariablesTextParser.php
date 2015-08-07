<?php

namespace ride\library\cms\content\text;

use ride\library\cms\content\text\variable\VariableParser;

/**
 * Text parser to replace variable placeholders with their values
 */
class VariablesTextParser extends AbstractTextParser {

    /**
     * Characters to open a variable placeholder
     * @var string
     */
    const OPEN = '[[';

    /**
     * Characters to close a variable placeholder
     * @var string
     */
    const CLOSE = ']]';

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

        $offset = 0;
        $lengthOpen = strlen(self::OPEN);
        $lengthClose = strlen(self::CLOSE);

        do {
            $positionOpen = strpos($text, self::OPEN, $offset);
            if ($positionOpen === false) {
                break;
            }

            $positionClose = strpos($text, self::CLOSE, $positionOpen);
            if ($positionClose === false) {
                break;
            }

            $variable = substr($text, $positionOpen + $lengthOpen, $positionClose - $positionOpen - $lengthClose);
            $textBefore = substr($text, 0, $positionOpen);
            $textAfter = substr($text, $positionClose + $lengthClose);

            $text = $textBefore . $this->getParsedVariable($variable);

            $offset = strlen($text);

            $text .= $textAfter;
        } while ($positionOpen !== false);

        return $text;
    }

    /**
     * Gets the value of the provided variable
     * @param array $matches The matches of the variable regular expression
     * @return string The value of the provided variable
     * @throws \ride\library\cms\exception\CmsException when an unsupported
     * variable is provided
     */
    protected function getParsedVariable($variable) {
        foreach ($this->variableParsers as $variableParser) {
            $value = $variableParser->parseVariable($variable);
            if ($value !== null) {
                return $value;
            }
        }

        return self::OPEN . $variable . self::CLOSE;
    }

}
