<?php

namespace ride\library\cms\content\text;

use ride\library\cms\node\Node;

/**
 * Interface to parse text
 */
interface TextParser {

    /**
     * Sets the current node
     * @param \ride\library\cms\node\Node $node Current node
     * @return null
     */
    public function setNode(Node $node);

    /**
     * Sets the current locale
     * @param string $locale Code of the locale
     * @return null
     */
    public function setLocale($locale);

    /**
     * Sets the base URL
     * @param string $baseURL Base URL
     * @return null
     */
    public function setBaseUrl($baseUrl);

    /**
     * Parses a text for display
     * @param string $text Text to parse
     * @return string Parsed text
     */
    public function parseText($text);

}
