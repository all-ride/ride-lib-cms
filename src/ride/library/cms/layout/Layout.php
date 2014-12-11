<?php

namespace ride\library\cms\layout;

/**
 * Interface for a predefined page layout
 */
interface Layout {

    /**
     * Gets the machine name of the layout
     * @return string
     */
    public function getName();

    /**
     * Gets the frontend template resource of the layout
     * @return string
     */
    public function getFrontendResource();

    /**
     * Gets the backend template resource of the layout
     * @return string
     */
    public function getBackendResource();

    /**
     * Checks if a block exists in this layout
     * @param string $block Name of the block
     * @return boolean
     */
    public function hasBlock($block);

    /**
     * Gets the blocks for this layout
     * @return array Array with the block name as key and as value
     */
    public function getBlocks();

}
