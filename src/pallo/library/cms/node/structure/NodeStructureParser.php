<?php

namespace pallo\library\cms\node\structure;

use pallo\library\cms\node\NodeModel;
use pallo\library\cms\node\Node;

/**
 * Parser for node structure from and into text format
 */
interface NodeStructureParser {

    /**
     * Gets the node tree in a text format
     * @param string $locale Locale of the structure
     * @param pallo\library\cms\node\SiteNode $site Site node
     * @return string Site node tree in text format
     */
    public function getStructure($locale, Node $site);

    /**
     * Saves the node tree from the structure in text format
     * @param string $locale Locale of the structure
     * @param pallo\library\cms\node\SiteNode $site Site node
     * @param pallo\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @param string $structure Site node tree in text format
     * @return null
     */
    public function setStructure($locale, Node $site, NodeModel $nodeModel, $structure);

}