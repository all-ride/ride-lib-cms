<?php

namespace ride\library\cms\node\structure;

use ride\library\cms\node\type\PageNodeType;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\Node;
use ride\library\cms\node\SiteNode;

/**
 * Parser for node structure from and into text format
 */
class GenericNodeStructureParser implements NodeStructureParser {

    /**
     * Gets the node tree in a text format
     * @param string $locale Locale of the structure
     * @param \ride\library\cms\node\Node $parent Parent node of the structure
     * @return string Node tree in text format
     */
    public function getStructure($locale, Node $parent) {
        if ($parent->getRootNode()->isLocalizationMethodUnique()) {
            $isUniqueTree = true;
        } else {
            $isUniqueTree = false;
        }

        $structure = '';

        $children = $parent->getChildren();
        foreach ($children as $child) {
            if ($isUniqueTree && !$child->isAvailableInLocale($locale)) {
                continue;
            }

            $structure .= $child->getName($locale);
            $structure .= ' [' . $child->getRoute($locale) . '|' . $child->getType() . '|' . $child->getId() . ']';
            $structure .= "\n";

            $childStructure = $this->getStructure($locale, $child);
            if ($childStructure) {
                $structure .= rtrim('    ' . str_replace("\n", "\n    ", $childStructure)) . "\n";
            }
        }

        return $structure;
    }

    /**
     * Saves the node tree from the structure in text format
     * @param string $locale Locale of the structure
     * @param \ride\library\cms\node\Node $parent Parent node of the structure
     * @param \ride\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @param string $structure Node tree in text format
     * @return null
     */
    public function setStructure($locale, Node $parent, NodeModel $nodeModel, $structure) {
        $site = $parent->getRootNode();
        if ($site->isLocalizationMethodUnique()) {
            $isUniqueTree = true;
        } else {
            $isUniqueTree = false;
        }

        $previousNodeId = null;
        $previousSpaces = null;

        $level = 0;
        $levels = array(
            0 => $site->getId(),
        );

        $order = array(
            $site->getId() => 0,
        );

        $spaces = array();

        $structure = $this->parseStructure($structure);
        foreach ($structure as $index => $nodeArray) {
            $structure[$index]['node'] = $this->saveNode($locale, $site, $nodeModel, $nodeArray, $isUniqueTree);
            $structure[$index]['id'] = $structure[$index]['node']->getId();

            if ($previousSpaces === null) {
                $spaces[$level] = $nodeArray['spaces'];
            } elseif ($nodeArray['spaces'] > $previousSpaces) {
                $level++;

                $levels[$level] = $previousNodeId;
                $spaces[$level] = $nodeArray['spaces'];
            } elseif ($nodeArray['spaces'] < $previousSpaces) {
                krsort($spaces);

                foreach ($spaces as $spaceLevel => $numSpaces) {
                    if ($nodeArray['spaces'] >= $numSpaces) {
                        $level = $spaceLevel;

                        break;
                    }
                }
            }

            $order[$levels[$level]]++;
            $order[$structure[$index]['id']] = 0;

            $previousNodeId = $structure[$index]['id'];
            $previousSpaces = $nodeArray['spaces'];
        }

        $siblings = $nodeModel->getNodesByPath($site->getId(), $site->getRevision(), $parent->getId());
        foreach ($siblings as $siblingId => $sibling) {
            if (isset($order[$siblingId]) || ($isUniqueTree && !$sibling->isAvailableInLocale($locale))) {
                continue;
            }

            $nodeModel->removeNode($sibling, false, null, false);
        }

        unset($order[$parent->getId()]);

        $nodeModel->orderNodes($site->getId(), $site->getRevision(), $parent->getId(), $order, $locale);
    }

    /**
     * Saves the node in the model
     * @param string $locale Locale of the structure
     * @param \ride\library\cms\node\SiteNode $site Site node
     * @param \ride\library\cms\node\NodeModel $nodeModel
     * @param array $nodeArray
     * @return \ride\library\cms\node\Node
     */
    protected function saveNode($locale, SiteNode $site, NodeModel $nodeModel, array $nodeArray, $isUniqueTree) {
        if (isset($nodeArray['id']) && $nodeArray['id']) {
            $node = $nodeModel->getNode($site->getId(), $site->getRevision(), $nodeArray['id']);
        } else {
            $type = $nodeArray['type'];
            if (!$type) {
                $type = PageNodeType::NAME;
            }

            $node = $nodeModel->createNode($type);
            $node->setParentNode($site);
            $node->setRevision($site->getRevision());

            if ($isUniqueTree) {
                $node->setAvailableLocales($locale);
            }
        }

        $node->setName($locale, $nodeArray['name']);
        if ($nodeArray['route'] && $nodeArray['route'] != '/nodes/' . $node->getId() . '/' . $locale) {
            $node->setRoute($locale, $nodeArray['route']);
        }

        $nodeModel->setNode($node, 'Updated structure of ' . $site->getName());

        return $node;
    }

    /**
     * Parses the lines of the structure
     * @param string $structure Site node tree in text format
     * @return array Array with node arrays
     */
    protected function parseStructure($structure) {
        $lines = explode("\n", $structure);

        $structure = array();

        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }

            $structure[] = $this->parseLine($line);
        }

        return $structure;
    }

    /**
     * Parses the different values from a line
     * @param string $line Line to parse
     * @return array Array with the name, route, type and number of spaces
     */
    protected function parseLine($line) {
        $line = rtrim($line);
        $numSpaces = strlen($line);
        $line = trim($line);
        $numSpaces -= strlen($line);

        $positionBracket = strpos($line, '[');
        if ($positionBracket === false) {
            $name = $line;
            $route = null;
            $type = null;
            $id = null;
        } else {
            $name = trim(substr($line, 0, $positionBracket));

            $line = trim(substr(str_replace(']', '', $line), $positionBracket + 1));

            $positionPipe = strpos($line, '|');
            if ($positionPipe === false) {
                $route = $line;
                $type = null;
                $id = null;
            } else {
                list($route, $type) = explode('|', $line, 2);

                $positionPipe = strpos($type, '|');
                if ($positionPipe === false) {
                    $id = null;
                } else {
                    list($type, $id) = explode('|', $type);
                }
            }
        }

        return array(
            'id' => trim($id),
            'name' => $name,
            'spaces' => $numSpaces,
            'route' => trim($route),
            'type' => trim($type),
        );
    }

}
