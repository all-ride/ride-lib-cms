<?php

namespace ride\library\cms\node;

use ride\library\cms\exception\CmsException;

/**
 * Data container for a homepage definition
 */
class HomePage {

    /**
     * Id of the homepage node
     * @var string
     */
    private $nodeId;

    /**
     * Timestamp of the start date
     * @var integer
     */
    private $dateStart;

    /**
     * Timestamp of the stop date
     * @var integer
     */
    private $dateStop;

    /**
     * Constructs a new homepage definition
     * @param string $nodeId Id of the homepage node
     * @param integer|null $dateStart Timestamp of the start date
     * @param integer|null $dateStop Timestamp of the stop date
     * @return null
     */
    public function __construct($nodeId, $dateStart = null, $dateStop = null) {
        $this->nodeId = $nodeId;
        $this->setDateStart($dateStart);
        $this->setDateStop($dateStop);
    }

    /**
     * Gets the id of the homepage node
     * @return string
     */
    public function getNodeId() {
        return $this->nodeId;
    }

    /**
     * Sets the start date
     * @param integer|null $dateStart Timestamp of the start date
     * @return null
     * @throws \ride\library\cms\exception\CmsException when an invalid date
     * has been provided
     */
    public function setDateStart($dateStart) {
        if ($dateStart !== null && !is_integer($dateStart)) {
            throw new CmsException('Could not set date start: null or a timestamp expected');
        }

        $this->dateStart = $dateStart;
    }

    /**
     * Gets the start date
     * @return integer|null Timestamp of the start date or null when not set
     */
    public function getDateStart() {
        return $this->dateStart;
    }

    /**
     * Sets the stop date
     * @param integer|null $dateStop Timestamp of the stop date
     * @return null
     * @throws \ride\library\cms\exception\CmsException when an invalid date
     * has been provided
     */
    public function setDateStop($dateStop) {
        if ($dateStop !== null && !is_integer($dateStop)) {
            throw new CmsException('Could not set date stop: null or a timestamp expected');
        }

        $this->dateStop = $dateStop;
    }

    /**
     * Gets the stop date
     * @return integer|null Timestamp of the stop date or null when not set
     */
    public function getDateStop() {
        return $this->dateStop;
    }

    /**
     * Gets whether this homepage is active
     * @param integer $time Timestamp of the date to check, defaults to now
     * @return boolean True if active, false otherwise
     */
    public function isActive($time = null) {
        if ($time === null) {
            $time = time();
        }

        if ($this->dateStart && $this->dateStop) {
            if ($this->dateStart <= $time && $time < $this->dateStop) {
                return true;
            }
        } elseif ($this->dateStart) {
            if ($this->dateStart <= $time) {
                return true;
            }
        } elseif ($this->dateStop) {
            if ($time < $this->dateStop) {
                return true;
            }
        }

        return false;
    }

}
