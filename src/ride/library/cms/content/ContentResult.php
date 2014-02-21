<?php

namespace ride\library\cms\content;

use \Iterator;

/**
 * Search result for a content type
 */
class ContentResult implements Iterator {

	/**
	 * Array with Content objects
	 * @var array
	 */
    protected $results;

    /**
     * Number of items in the results array
     * @var integer
     */
    protected $numResults;

    /**
     * Total number of items in the search result for this content type
     * @var integer
     */
    protected $totalNumResults;

    /**
     * Constructs a new search result for a content type
     * @param array $results Array with content objects
     * @param integer $totalNumResults Total number of items in the search result
     * @return null
     */
    public function __construct(array $results, $totalNumResults = null) {
    	$this->setResults($results);
    	$this->setTotalNumResults($totalNumResults);
    }

    /**
     * Sets the results
     * @param array $results Array with content objects
     * @return null
     */
    private function setResults(array $results) {
    	$this->results = $results;
    	$this->numResults = count($results);
    }

    /**
     * Gets the results
     * @return array Array with content objects
     */
    public function getResults() {
        return $this->results;
    }

    /**
     * Gets the number of results
     * @return integer
     */
    public function getNumResults() {
        return $this->numResults;
    }

    /**
     * Sets the total number of items in the search result
     * @param integer $totalNumResults
     * @return null
     */
    private function setTotalNumResults($totalNumResults) {
    	$this->totalNumResults = $totalNumResults;
    }

    /**
     * Gets the total number of items in the search result
     * @return integer
     */
    public function getTotalNumResults() {
        if ($this->totalNumResults !== null) {
            return $this->totalNumResults;
        }

        return $this->numResults;
    }

    /**
     * Rewinds the position pointer of the results array
     * @return boolean True on success, false on failure
     */
    public function rewind() {
        return reset($this->results);
    }

    /**
     * Gets the current element of the result
     * @return mixed Current element value on success, false on failure
     */
    public function current() {
        return current($this->results);
    }

    /**
     * Gets the current key of the result
     * @return mixed Current element key on success, false on failure
     */
    public function key() {
        return key($this->results);
    }
    /**
     * Gets the next element of the result
     * @return mixed Next element on success, false on failure
     */
    public function next() {
        return next($this->results);
    }

    /**
     * Checks if current position is valid
     * @return boolean True if valid, false otherwise
     */
    public function valid() {
        return key($this->results) !== null;
    }

}