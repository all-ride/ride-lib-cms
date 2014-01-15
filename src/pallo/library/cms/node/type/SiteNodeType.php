<?php

namespace pallo\library\cms\node\type;

use pallo\library\cms\node\SiteNode;
use pallo\library\cms\exception\CmsException;

/**
 * Implementation of the page node type
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
     * Sets the default theme of a new node
     * @param string $defaultTheme
     * @return null
     */
    public function setDefaultTheme($defaultTheme) {
        if ($defaultTheme !== null && (!is_string($defaultTheme) || $defaultTheme == '')) {
            throw new CmsException('Could not set the default theme: invalid argument provided');
        }

        $this->defaultTheme = $defaultTheme;
    }

    /**
     * Creates a new node of this type
     * @return pallo\library\cms\node\Node
     */
    public function createNode() {
        $site = new SiteNode();

        if ($this->defaultTheme) {
            $site->setTheme($this->defaultTheme);
        }

        return $site;
    }

}