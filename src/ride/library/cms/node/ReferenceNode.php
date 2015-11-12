<?php

namespace ride\library\cms\node;

use ride\library\cms\node\type\ReferenceNodeType;

/**
 * Node implementation for a reference
 */
class ReferenceNode extends Node {

    /**
     * Property key for the reference node
     * @var string
     */
    const PROPERTY_NODE = 'reference.node';

    protected $node;

    /**
     * Constructs a new reference node
     * @return null
     */
    public function __construct() {
        parent::__construct(ReferenceNodeType::NAME);

        $this->defaultInherit = true;
    }

    /**
     * Sets the reference node
     * @param string $node Id of a node
     * @return null
     */
    public function setReferenceNode($node) {
        $this->set(self::PROPERTY_NODE, $node);
    }

    /**
     * Gets the reference node
     * @return string|null The id of the node
     */
    public function getReferenceNode() {
        return $this->get(self::PROPERTY_NODE);
    }

    /**
     * Sets the referenced node
     * @param Node $node
     */
    public function setNode(Node $node) {
        $this->node = $node;
    }

    /**
     * Gets the references node
     * @return Node
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Gets the name of this node for the provided locale
     * @param string $locale Code of the locale
     * @param string $context Name of the context (menu, breadcrumb, title, ...)
     * @return string The name of this node
     */
    public function getName($locale = null, $context = null) {
        $name = parent::getName($locale, $context);
        if ($name) {
            return $name;
        }

        $node = $this->getNode();
        if ($node) {
            return $node->getName($locale, $context);
        }
    }

    /**
     * Set the name of this node for the provided locale
     * @param string $locale Code of the locale
     * @param string $name Name of the node in the provided locale
     * @param string $context Name of the context (menu, breadcrumb, title, ...)
     * @return null
     */
    public function setName($locale, $name, $context = null) {
        if ($this->getName($locale, $context) == $name) {
            return;
        } else {
            parent::setName($locale, $name, $context);
        }
    }

    /**
     * Gets the description of this node
     * @param string $locale Code of the locale
     * @return string|null Description of this node if set, null otherwise
     */
    public function getDescription($locale) {
        $description = parent::getDescription($locale);
        if ($description) {
            return $description;
        }

        $node = $this->getNode();
        if ($node) {
            return $node->getDescription($locale);
        }
    }

    /**
     * Gets the image of this node
     * @param string $locale Code of the locale
     * @return string|null Path to the image if set, null otherwise
     */
    public function getImage($locale) {
        $image = parent::getImage($locale);
        if ($image) {
            return $image;
        }

        $node = $this->getNode();
        if ($node) {
            return $node->getImage($locale);
        }
    }

    /**
     * Get the route of this node. The route is used in the frontend as an url
     * alias.
     * @param string $locale Code of the locale
     * @param boolean $returnDefault Set to false to return null when the route
     * is not set
     * @return string
     */
    public function getRoute($locale, $returnDefault = true) {
        $node = $this->getNode();
        if ($node) {
            return $node->getRoute($locale, $returnDefault);
        }
    }

    /**
     * Gets the set routes of this node
     * @return array Array with the locale code as key and the route as value
     */
    public function getRoutes() {
        $node = $this->getNode();
        if ($node) {
            return $node->getRoutes();
        }
    }

}
