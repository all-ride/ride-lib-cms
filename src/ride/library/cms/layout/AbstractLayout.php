<?php

namespace ride\library\cms\layout;

/**
 * Abstract layout
 */
abstract class AbstractLayout implements Layout {

    /**
     * Block names
     * @var array
     */
    protected $blocks;

    /**
     * Gets the machine name of the layout
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Gets the frontend template resource of the layout
     * @return string
     */
    public function getFrontendResource() {
        return 'cms/frontend/layout/' . static::NAME;
    }

    /**
     * Gets the backend template resource of the layout
     * @return string
     */
    public function getBackendResource() {
        return 'cms/backend/layout/' . static::NAME;
    }


    /**
     * Checks if a block exists in this layout
     * @return boolean
     */
    public function hasBlock($block) {
        return isset($this->blocks[$block]);
    }

    /**
     * Gets the blocks for this layout
     * @return array Array with the block name as key and as value
     */
    public function getBlocks() {
        return $this->blocks;
    }

}
