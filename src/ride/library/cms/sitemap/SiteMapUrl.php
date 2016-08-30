<?php

namespace ride\library\cms\sitemap;

use ride\library\cms\exception\CmsException;

/**
 * Data container for a site map URL
 */
class SiteMapUrl {

    /**
     * Always change frequency
     * @var string
     */
    const FREQUENCY_ALWAYS = 'always';

    /**
     * Hourly change frequency
     * @var string
     */
    const FREQUENCY_HOURLY = 'hourly';

    /**
     * Daily change frequency
     * @var string
     */
    const FREQUENCY_DAILY = 'daily';

    /**
     * Weekly change frequency
     * @var string
     */
    const FREQUENCY_WEEKLY = 'weekly';

    /**
     * Monthly change frequency
     * @var string
     */
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * Yearly change frequency
     * @var string
     */
    const FREQUENCY_YEARLY = 'yearly';

    /**
     * Never change frequency
     * @var string
     */
    const FREQUENCY_NEVER = 'never';

    /**
     * URL of the page
     * @var string
     */
    private $url;

    /**
     * Timestamp of the last modification
     * @var integer
     */
    private $lastModified;

    /**
     * Change frequency of the URL. Should be one of the FREQUENCY_ constants.
     * @var string
     */
    private $changeFrequency;

    /**
     * Priority of this URL relative to other URL's in the site. Value between
     * 0 and 1.
     * @var float
     */
    private $priority;

    /**
     * Constructs a new site map URL
     * @param string $url URL of the page
     * @param integer $lastModified Timestamp of the last modification
     * @param string $changeFrequency Change frequency of the URL. Should be one
     * of the FREQUENCY_ constants.
     * @param float $priority Priority of this URL relative to other URL's in
     * the site. Value between 0 and 1.
     * @return null
     */
    public function __construct($url, $lastModified = null, $changeFrequency = null, $priority = null) {
        $this->setUrl($url);
        $this->setLastModified($lastModified);
        $this->setChangeFrequency($changeFrequency);
        $this->setPriority($priority);
    }

    /**
     * Gets a string representation of this site map URL
     * @return string
     */
    public function __toString() {
        return $this->getXml();
    }

    /**
     * Sets the URL of the page
     * @param string $url URL of the page
     * @throws \ride\library\cms\exception\CmsException when the provided value
     * is invalid
     */
    private function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Gets the URL of the page
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Sets the last modification date of this URL
     * @param integer $lastModified Timestamp of the modification date
     * @return null
     * @throws \ride\library\cms\exception\CmsException when the provided value
     * is invalid
     */
    private function setLastModified($lastModified) {
        if (!is_numeric($lastModified)) {
           throw new CmsException('Could not set last modification date of the site map URL: value should be a timestamp');
        }

        $this->lastModified = $lastModified;
    }

    /**
     * Gets the last modification date of this URL
     * @return integer|null Timestamp of the modification date if set, null
     * otherwise
     */
    public function getLastModified() {
        return $this->lastModified;
    }

    /**
     * Sets the change frequency of this URL
     * @param string $changeFrequency One of the FREQUENCY_ constants
     * @return null
     * @throws \ride\library\cms\exception\CmsException when the provided value
     * is invalid
     */
    private function setChangeFrequency($changeFrequency) {
        if ($changeFrequency !== null
            && $changeFrequency !== self::FREQUENCY_ALWAYS
            && $changeFrequency !== self::FREQUENCY_HOURLY
            && $changeFrequency !== self::FREQUENCY_DAILY
            && $changeFrequency !== self::FREQUENCY_WEEKLY
            && $changeFrequency !== self::FREQUENCY_MONTHLY
            && $changeFrequency !== self::FREQUENCY_YEARLY
            && $changeFrequency !== self::FREQUENCY_NEVER
        ) {
            throw new CmsException('Could not set the change frequency of the site map URL: value should be one of the FREQUENCY_ constants.');
        }

        $this->changeFrequency = $changeFrequency;
    }

    /**
     * Gets the change frequency of this URL
     * @return string|null One of the FREQUENCY_ constants when set, null
     * otherwise
     */
    public function getChangeFrequency() {
        return $this->changeFrequency;
    }

    /**
     * Sets the priority of this URL relative to other URL's in the site
     * @param float $priority Null or a value between 0 and 1
     * @return null
     * @throws \ride\library\cms\exception\CmsException when the provided value
     * is invalid
     */
    private function setPriority($priority) {
        if ($priority !== null && (!is_numeric($priority) || $priority < 0 || $priority > 1)) {
            throw new CmsException('Could not set priority of the site map URL: value should be a numeric value between 0 and 1');
        }

        $this->priority = $priority;
    }

    /**
     * Gets the priority of this URL relative to other URL's of the site
     * @return float|null Value between 0 and 1 if set, null otherwise
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Gets the XML element for this URL
     * @return string
     */
    public function getXml() {
        $xml = '<url>';
        $xml .= '<loc>' . htmlentities($this->url) . '</loc>';
        if ($this->lastModified !== null) {
            $xml .= '<lastmod>' . date('c', $this->lastModified) . '</lastmod>';
        }
        if ($this->changeFrequency !== null) {
            $xml .= '<changefreq>' . $this->changeFrequency . '</changefreq>';
        }
        if ($this->priority !== null) {
            $xml .= '<priority>' . $this->changeFrequency . '</priority>';
        }
        $xml .= '</url>';

        return $xml;
    }

}
