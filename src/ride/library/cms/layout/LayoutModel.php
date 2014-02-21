<?php

namespace ride\library\cms\layout;

/**
 * Model of the available layouts
 */
interface LayoutModel {

    /**
     * Gets the a specific layout
     * @param string $layout Machine name of the layout
     * @return Layout
     */
    public function getLayout($layout);

    /**
     * Gets the available layouts
     * @return array Array with the machine name of the layout as key and an
     * instance of Layout as value
     */
    public function getLayouts();

}