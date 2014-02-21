<?php

namespace ride\library\cms\node\structure;

use ride\library\cms\node\NodeModel;
use ride\library\cms\node\Node;

/**
 * Parser for node structure from and into text format
 */
interface NodeStructureParser {

    /**
     * Gets the node tree in a text format
     * @param string $locale Locale of the structure
     * @param ride\library\cms\node\SiteNode $site Site node
     * @return string Site node tree in text format
     */
    public function getStructure($locale, Node $site);

    /**
     * Saves the node tree from the structure in text format
     * @param string $locale Locale of the structure
     * @param ride\library\cms\node\SiteNode $site Site node
     * @param ride\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @param string $structure Site node tree in text format
     * @return null
     */
    public function setStructure($locale, Node $site, NodeModel $nodeModel, $structure);

}