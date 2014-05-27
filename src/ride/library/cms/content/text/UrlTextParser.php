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
            $url = $element->$attribute;
            if ($url{0} == '#' || strncmp($url, 'mailto:', 7) === 0 || strncmp($url, 'http:', 5) === 0 || strncmp($url, 'https:', 6) === 0 || ($url{0} == '/' && $url{1} == '/')) {
                continue;
            }

            if (strncmp($url, './', 2) === 0) {
                $baseUrl = $this->node->getUrl($this->locale, $this->baseUrl);
                $baseUrl = rtrim($baseUrl, '/');

                $position = strrpos($baseUrl, '/');

                $baseUrl = substr($baseUrl, 0, $position);
                $url = substr($url, 2);

                $url = $baseUrl . '/' . $url;
            } elseif (strncmp($url, '../', 3) === 0) {
                $baseUrl = $this->node->getUrl($this->locale, $this->baseUrl);
                $baseUrl = rtrim($baseUrl, '/');

                $position = strrpos($baseUrl, '/');
                $baseUrl = substr($baseUrl, 0, $position);

                do {
                    $position = strrpos($baseUrl, '/');
                    if ($position === false) {
                        break;
                    }

                    $baseUrl = substr($baseUrl, 0, $position);
                    $url = substr($url, 3);
                } while (strncmp($url, '../', 3) === 0);

                $url = $baseUrl . '/' . $url;
            } else {
                $url = $this->baseUrl . '/' . ltrim($element->$attribute, '/');
            }

            $element->$attribute = $url;
        }
    }

}
