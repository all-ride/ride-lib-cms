<?php

namespace ride\library\cms\node;

use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\type\HomeNodeType;

use \DateTime;

/**
 * Node implementation for a home page
 */
class HomeNode extends Node {

    /**
     * Prefix for the home properties
     * @var string
     */
    const PROPERTY_HOME = 'home';

    /**
     * Name of the default page
     * @var string
     */
    const PROPERTY_DEFAULT = 'default';

    /**
     * Property suffix for the node id of a home page
     * @var string
     */
    const PROPERTY_HOME_NODE = 'node';

    /**
     * Property suffix for the start date of a home page
     * @var string
     */
    const PROPERTY_HOME_START = 'start';

    /**
     * Property suffix for the stop date of a home page
     * @var string
     */
    const PROPERTY_HOME_STOP = 'stop';

    /**
     * Constructs a new home node
     * @return null
     */
    public function __construct() {
        parent::__construct(HomeNodeType::NAME);

        $this->defaultInherit = true;
    }

    /**
     * Sets the route of this node for the provided locale
     * @param string $locale The code of the locale
     * @param string $route The route of this node
     * @return null
     */
    public function setRoute($locale, $route) {
        // route for a home page is always /
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
        return '/';
    }

    /**
     * Sets the default home page for the provided locale
     * @param string $locale Code of the locale
     * @param string $node Id of a node
     * @return null
     */
    public function setDefaultHomePage($locale, $node) {
        $this->set(self::PROPERTY_HOME . '.' . $locale . '.' . self::PROPERTY_DEFAULT, $node);
    }

    /**
     * Gets the default home page for the provided locale
     * @param string $locale Code of the locale
     * @return string|null The id of the node
     */
    public function getDefaultHomePage($locale) {
        return $this->get(self::PROPERTY_HOME . '.' . $locale . '.' . self::PROPERTY_DEFAULT);
    }

    /**
     * Sets a scheduled homepage
     * @param string $locale Code of the locale
     * @param array $homePages Array with HomePage instances
     * @return null
     * @see \ride\library\cms\node\HomePage
     * @throws \ride\library\cms\exception\CmsException when a non HomePage
     * instance was provided
     */
    public function setHomePages($locale, array $homePages) {
        $prefix = self::PROPERTY_HOME . '.' . $locale . '.';

        // remove properties of the current homepages
        $properties = $this->getProperties($prefix);
        foreach ($properties as $key => $null) {
            if ($key === $prefix . self::PROPERTY_DEFAULT) {
                continue;
            }

            $this->set($key, null);
        }

        // set properties for the provided homepages
        $index = 0;
        $homePages = array_values($homePages);
        foreach ($homePages as $homePage) {
            if (!$homePage instanceof HomePage) {
                throw new CmsException('Could not set the homepages: non HomePage instance found on index ' . $index);
            }

            $id = $index + 1;
            $homePagePrefix = $prefix . $id . '.';

            $this->set($homePagePrefix . self::PROPERTY_HOME_NODE, $homePage->getNodeId());
            if ($homePage->getDateStart()) {
                $this->set($homePagePrefix . self::PROPERTY_HOME_START, date(NodeProperty::DATE_FORMAT, $homePage->getDateStart()));
            }
            if ($homePage->getDateStop()) {
                $this->set($homePagePrefix . self::PROPERTY_HOME_STOP, date(NodeProperty::DATE_FORMAT, $homePage->getDateStop()));
            }
        }
    }

    /**
     * Gets the scheduled homepages for the provided locale
     * @param string $locale Code of the locale
     * @return array Array with HomePage instances
     * @see \ride\library\cms\node\HomePage
     */
    public function getHomePages($locale) {
        $homePages = array();

        // retrieve properties
        $prefix = self::PROPERTY_HOME . '.' . $locale . '.';

        $properties = $this->getProperties($prefix);
        foreach ($properties as $key => $property) {
            $key = str_replace($prefix, '', $key);
            if ($key === self::PROPERTY_DEFAULT || !strpos($key, '.')) {
                continue;
            }

            list($id, $homeProperty) = explode('.', $key);

            if (isset($homePages[$id])) {
                $homePages[$id][$homeProperty] = $property->getValue();
            } else {
                $homePages[$id] = array($homeProperty => $property->getValue());
            }
        }

        // convert to objects
        foreach ($homePages as $id => $properties) {
            if (!isset($properties[self::PROPERTY_HOME_NODE])) {
                unset($homePages[$id]);

                continue;
            }

            $homePage = new HomePage($properties[self::PROPERTY_HOME_NODE]);
            if (isset($properties[self::PROPERTY_HOME_START])) {
                $date = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $properties[self::PROPERTY_HOME_START]);
                if ($date) {
                    $homePage->setDateStart($date->getTimestamp());
                }
            }
            if (isset($properties[self::PROPERTY_HOME_STOP])) {
                $date = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $properties[self::PROPERTY_HOME_STOP]);
                if ($date) {
                    $homePage->setDateStop($date->getTimestamp());
                }
            }

            $homePages[$id] = $homePage;
        }

        return $homePages;
    }

    /**
     * Gets the active home page
     * @param NodeModel $nodeModel Instance of the node model
     * @param string $locale Code of the locale
     * @param integer $time Timestamp for the homepage, current time will be
     * used when this parameter is not provided
     * @return string|null Node id of the real home page, null if no homepage
     * set
     */
    public function getHomePage(NodeModel $nodeModel, $locale, $time = null) {
        if ($time === null) {
            $time = time();
        }

        // filter out inactive home pages
        $homePages = $this->getHomePages($locale);
        foreach ($homePages as $index => $homePage) {
            if ($homePage->isActive($time)) {
                $homePages[$index] = $homePage->getNodeId();
            } else {
                unset($homePages[$index]);
            }
        }

        if (!$homePages) {
            // fallback on default homepage
            $nodeId = $this->getDefaultHomePage($locale);
            if (!$nodeId) {
                return null;
            }

            $homePages = array($nodeId);
        }

        // take the first valid one home page
        do {
            $nodeId =  array_shift($homePages);

            try {
                $node = $nodeModel->getNode($this->getRootNodeId(), $this->getRevision(), $nodeId);
            } catch (NodeNotFoundException $exception) {
                $node = null;
            }
        } while ($homePages && !$node);

        return $node;
    }

}
