<?php

namespace ride\library\cms\content;

use ride\library\cms\node\exception\NodeNotFoundException;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\Node;

use \simple_html_dom;

/**
 * Parser for user inputted texts
 */
class TextParser {

	/**
	 * Regular expression for a node variable
	 * @var string
	 */
	const REGEX_VARIABLE = '/%(([a-zA-Z0-9]*)\\.)*([a-zA-Z0-9]*)%/';

	/**
	 * Name of the name variable
	 * @var string
	 */
	const VARIABLE_NAME = 'name';

	/**
	 * Name of the url variable
	 * @var string
	 */
	const VARIABLE_URL = 'url';

	/**
	 * Model of the nodes
	 * @var ride\library\cms\node\NodeModel
	 */
	protected $nodeModel;

	/**
	 * Context for the site variables
	 * @var ride\library\cms\node\Node
	 */
	protected $context;

	/**
	 * Code of the current locale
	 * @var string
	 */
	protected $locale;

	/**
	 * Base URL of the site
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * Base script to generate node URLs
	 * @var string
	 */
	protected $baseScript;

	/**
	 * Constructs a new text parser
	 * @param ride\library\cms\node\NodeModel $nodeModel Instance of the node
	 * model
	 * @param ride\library\cms\node\Node $context Context for the site
	 * variables
	 * @param string $locale Code of the current locale
	 * @param string $baseScript URL to the base script
	 * @return null
	 */
	public function __construct(NodeModel $nodeModel, Node $context, $locale, $baseScript) {
		$this->nodeModel = $nodeModel;
		$this->context = $context;
		$this->locale = $locale;
		$this->baseScript = $baseScript;
		$this->baseUrl = str_replace('/index.php', '', $baseScript);
	}

	/**
	 * Parses a user inputted text for display, parses variables and makes
	 * urls absolute
	 * @param string $text Text to parse
	 * @return string Parsed text
	 */
	public function parseText($text) {
	    if (!$text) {
	        return $text;
	    }

        $text = $this->parseVariables($text);
        $text = $this->parseUrls($text);

        return $text;
	}

	/**
	 * Parses a text to replace cms variables with their values. Syntax of a
	 * variable is %[site|node].<id>.<variable>% eg. %node.contact.url%
	 * @param string $text Text with CMS variables
	 * @return string Parsed text with the values of the CMS variables
	 */
	protected function parseVariables($text) {
        return preg_replace_callback(self::REGEX_VARIABLE, array($this, 'getParsedVariable'), $text);
	}

	/**
	 * Gets the value of the provided variable
	 * @param array $matches The matches of the variable regular expression
	 * @return string The value of the provided variable
	 * @throws ride\library\cms\exception\CmsException when an unsupported
	 * variable is provided
	 */
    protected function getParsedVariable(array $matches) {
        $tokens = explode('.', substr($matches[0], 1, -1));

        switch ($tokens[0]) {
            case 'node':
                if (count($tokens) < 3) {
                    return $matches[0];
                }

                try {
                    $node = $this->nodeModel->getNode($tokens[1], 0);
                } catch (NodeNotFoundException $exception) {
                    return $matches[0];
                }

                switch($tokens[2]) {
                	case self::VARIABLE_URL:
                		return $this->baseScript . $node->getRoute(isset($tokens[3]) ? $tokens[3] : $this->locale);

                	    break;
                	case self::VARIABLE_NAME:
                		return $node->getName(isset($tokens[3]) ? $tokens[3] : $this->locale);

                		break;
                	default:
                		throw new CmsException($variableName . ' is not a supported CMS variable. Try name of url');
                }

                break;
            case 'site':
                if (count($tokens) < 2) {
                }

                switch ($tokens[1]) {
                    case self::VARIABLE_NAME:
                        return $this->context->getRootNode()->getName(isset($tokens[2]) ? $tokens[2] : $this->locale);

                        break;
                    case self::VARIABLE_URL:
                        return str_replace('index.php', '', $this->baseScript);
                	default:
                		throw new CmsException($variablename . ' is not a supported cms variable. try name of url');
                }

                break;
        }

        return $matches[0];
    }

    /**
     * Parse the URLs from the provided text
     * @param string $text Text to parse
     * @return string Text with absolute URLS
     */
    protected function parseUrls($text) {
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
            $url = substr($element->$attribute, 0, 7);
            if ($url{0} == '#' || $url == 'mailto:' || $url == 'http://' || $url == 'https:/' || ($url{0} == '/' && $url{1} == '/')) {
                continue;
            }

            $element->$attribute = $this->baseUrl . '/' . ltrim($element->$attribute, '/');
        }
    }

}