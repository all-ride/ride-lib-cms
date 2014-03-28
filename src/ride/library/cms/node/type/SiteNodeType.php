<?php

namespace ride\library\cms\node\type;

use ride\library\cms\node\SiteNode;
use ride\library\cms\exception\CmsException;

/**
 * Implementation of the site node type
 */
class SiteNodeType extends AbstractNodeType {

    /**
     * Name of the type
     * @var string
     */
    const NAME = 'site';

    /**
     * Name of the default theme
     * @var string|null
     */
    protected $defaultTheme;

    /**
     * Sets the default theme of a new site node
     * @param string|null $defaultTheme Machine name of the theme
     * @return null
     */
    public function setDefaultTheme($defaultTheme) {
        if ($defaultTheme !== null && (!is_string($defaultTheme) || $defaultTheme == '')) {
            throw new CmsException('Could not set the default theme: invalid argument provided');
        }

        $this->defaultTheme = $defaultTheme;
    }

    /**
     * Gets the default theme of a new site node
     * @return string|null Machine name of the theme
     */
    public function getDefaultTheme() {
        return $this->defaultTheme;
    }

    /**
     * Creates a new node of this type
     * @return \ride\library\cms\node\Node
     */
    public function createNode() {
        $site = new SiteNode();

        if ($this->defaultTheme) {
            $site->setTheme($this->defaultTheme);
        }

        return $site;
    }

}