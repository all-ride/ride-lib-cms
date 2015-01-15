<?php

namespace ride\library\cms\content\text;

use ride\library\cms\node\Node;

/**
 * Text parser implementation to chain different implementations together
 */
class ChainTextParser implements TextParser {

    /**
     * Text parsers for the chain
     * @var array
     */
    protected $textParsers;

    /**
     * Adds a text parser to the chain
     * @param TextParser $textParser Instance to add
     * @return null
     */
    public function addTextParser(TextParser $textParser) {
        $this->textParsers[] = $textParser;
    }

    /**
     * Removes a text parser from the chain
     * @param TextParser $textParser Instance of the text parser which needs to
     * be removed
     * @return null
     */
    public function removeTextParser(TextParser $textParser) {
        foreach ($this->textParsers as $index => $loopTextParser) {
            if ($textParser === $loopTextParser) {
                unset($this->textParsers[$index]);
            }
        }
    }

    /**
     * Sets the current node
     * @param \ride\library\cms\node\Node $node Current node
     * @return null
     */
    public function setNode(Node $node) {
        foreach ($this->textParsers as $textParser) {
            $textParser->setNode($node);
        }
    }

    /**
     * Sets the current locale
     * @param string $locale Code of the locale
     * @return null
     */
    public function setLocale($locale) {
        foreach ($this->textParsers as $textParser) {
            $textParser->setLocale($locale);
        }
    }

    /**
     * Sets the base URL
     * @param string $baseURL Base URL
     * @return null
     */
    public function setBaseUrl($baseUrl) {
        foreach ($this->textParsers as $textParser) {
            $textParser->setBaseUrl($baseUrl);
        }
    }

    /**
     * Sets the base URL to the site
     * @param string $siteURL Site URL
     * @return null
     */
    public function setSiteUrl($siteUrl) {
        foreach ($this->textParsers as $textParser) {
            $textParser->setSiteUrl($siteUrl);
        }
    }

    /**
     * Parses a text for display
     * @param string $text Text to parse
     * @return string Parsed text
     */
    public function parseText($text) {
        foreach ($this->textParsers as $textParser) {
            $text = $textParser->parseText($text);
        }

        return $text;
    }

}
