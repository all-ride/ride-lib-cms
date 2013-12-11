<?php

namespace pallo\library\cms\widget;

use pallo\library\widget\Widget as LibraryWidget;

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
     * @see pallo\library\router\Route
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
     * Gets whether to display this widget as page
     * @return boolean True to only display this widget
     */
    public function isContent();

    /**
     * Gets whether this is the only widget to be displayed in the containing region
     * @return boolean True to only display this widget in the region
     */
    public function isRegion();

    /**
     * Gets whether this widget contains user content
     * @return boolean
     */
    public function containsUserContent();

    /**
     * Sets the context of the node
     * @param array $context
     * @return null
     */
    public function setContext(array $context);

    /**
     * Gets the context of the node
     * @return array
     */
    public function getContext();

}