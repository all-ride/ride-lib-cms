<?php

namespace ride\library\cms\widget;

use ride\library\widget\Widget as LibraryWidget;

/**
 * Interface for a widget: a small independant component for a page
 */
interface Widget extends LibraryWidget {

    /**
     * Sets the region for the widget request
     * @param string $region Name of the region
     * @return null
     */
    public function setRegion($region);

    /**
     * Gets the routes for this widget
     * @return array|null Array with Route objects
     * @see ride\library\router\Route
     */
    public function getRoutes();

    /**
     * Gets the templates used by this widget
     * @return array Array with the resource names of the templates
     */
    public function getTemplates();

    /**
     * Gets a human preview of the set properties
     * @return string
     */
    public function getPropertiesPreview();

    /**
     * Get the breadcrumbs of the page
     * @return array Array with the URL as key and the label as value
     */
    public function getBreadcrumbs();

    /**
     * Gets whether this widget caches when auto cache is enabled
     * @return boolean
     */
    public function isAutoCache();

    /**
     * Gets whether to display this widget as page
     * @return boolean True to only display this widget
     */
    public function isContent();

    /**
     * Gets whether this is the only widget to be displayed in the containing
     * region
     * @return boolean True to only display this widget in the region
     */
    public function isRegion();

    /**
     * Gets whether this is the only widget to be displayed in the containing
     * section
     * @return boolean True to only display this widget in the section
     */
    public function isSection();

    /**
     * Gets whether this is the only widget to be displayed in the containing
     * block
     * @return boolean True to only display this widget in the block
     */
    public function isBlock();

    /**
     * Gets whether this widget contains user content
     * @return boolean
     */
    public function containsUserContent();

    /**
     * Sets context to the widget
     * @param string|array $context Name of the context variable or an array
     * of key-value pairs
     * @param mixed $value Context value
     * @return null
     */
    public function setContext($context, $value = null);

    /**
     * Gets context from the widget
     * @param string $name Name of the context variable
     * @param mixed $default Default value for when the variable is not set
     * @return mixed Full context if no arguments provided, value of the
     * variable if set in the context, provided default value otherwise
     */
    public function getContext($name = null, $default = null);

}
