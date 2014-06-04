<?php

namespace ride\library\cms\content\text;

use \simple_html_dom;

/**
 * Text parser to replace relative URLs with absolute ones.
 */
class UrlTextParser extends AbstractTextParser {

    /**
     * Parses a text for display
     * @param string $text Text to parse
     * @return string Parsed text
     */
    public function parseText($text) {
        if (!$text || !is_string($text)) {
            return $text;
        }

        $html = new simple_html_dom();
        if ($html->load($text) === false) {
            return $text;
        }

        $anchors = $html->find('a');
        if ($anchors) {
            $this->replaceUrls($anchors, 'href');
        }

        $images = $html->find('img');
        if ($images) {
            $this->replaceUrls($images, 'src');
        }

        return (string) $html;
    }

    /**
     * Replace the url in the provided HTML elements
     * @param array $elements HTML elements with a URL attribute
     * @param string $attribute Name of the URL attribute
     * @return null
     */
    protected function replaceUrls(array $elements, $attribute) {
        foreach ($elements as $element) {
            $element->$attribute = $this->node->resolveUrl($this->locale, $this->baseUrl, $element->$attribute);
        }
    }

}
