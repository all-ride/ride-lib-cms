<?php

namespace ride\library\cms\content\text;

use voku\helper\HtmlDomParser;

use \Exception;

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

        try {
            $document = new HtmlDomParser($text);

            $anchors = $document->find('a');
            if ($anchors) {
                $this->replaceUrls($anchors, 'href', $this->siteUrl);
            }

            $images = $document->find('img');
            if ($images) {
                $this->replaceUrls($images, 'src', $this->baseUrl);
            }

            return $document->html();
        } catch (Exception $e) {
            return $text;
        }
    }

    /**
     * Replace the url in the provided HTML elements
     * @param array $elements HTML elements with a URL attribute
     * @param string $attribute Name of the URL attribute
     * @param string $baseUrl Base URL for the element
     * @return null
     */
    protected function replaceUrls($elements, $attribute, $baseUrl) {
        foreach ($elements as $element) {
            $element->$attribute = $this->node->resolveUrl($this->locale, $baseUrl, $element->$attribute);
        }
    }

}
