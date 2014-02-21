<?php

namespace ride\library\cms\node;

use ride\library\cms\exception\CmsException;

/**
 * Data container for a node property
 */
class NodeProperty {

    /**
     * Date format for properties
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Prefix for a setting key in the INI format when it should inherit to lower levels
     * @var string
     */
    const INHERIT_PREFIX = '_';

    /**
     * Separator for a list of values
     * @var string
     */
    const LIST_SEPARATOR = ',';

	/**
	 * Key of the property
	 * @var string
	 */
	protected $key;

	/**
	 * Value of the property
	 * @var string
	 */
	protected $value;

	/**
	 * Flag to set if this property should be inherited to lower levels
	 * @var boolean
	 */
	protected $inherit;

	/**
	 * Constructs a new node property
	 * @param string $key Key of the property
	 * @param string $value Value of the property
	 * @param boolean $inherit Flag to see if this property is being inherited
	 * to lower levels
	 * @return null
	 */
	public function __construct($key, $value, $inherit = false) {
	    $this->setKey($key);
	    $this->setValue($value);
	    $this->setInherit($inherit);
	}

	/**
	 * Sets the key of this property
	 * @param string $key
	 * @return null
	 */
    protected function setKey($key) {
        if (!is_string($key) || $key === '') {
            throw new CmsException('Provided key is empty or invalid');
        }

        $this->key = $key;
    }

    /**
     * Gets the key of this property
     * @return string
     */
	public function getKey() {
	    return $this->key;
	}

	/**
	 * Sets the value of this property
	 * @param string $value
	 * @return null
	 */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Gets the value of this property
     * @return string
     */
	public function getValue() {
	    return $this->value;
	}

	/**
	 * Sets whether this property is to be inherited
	 * @param boolean $inherit
	 * @return null
	 */
	public function setInherit($inherit) {
	    $this->inherit = $inherit;
	}

	/**
	 * Gets whether this property is to be inherited
	 * @return boolean
	 */
	public function getInherit() {
	    return $this->inherit;
	}

	/**
	 * Get a INI string for this property
	 * @param boolean $escapeHtml Set to true to escape the HTML in value
	 * @return string
	 */
	public function getIniString($escapeHtml = false) {
	    $ini = '';

	    if ($this->inherit) {
	        $ini .= self::INHERIT_PREFIX;
	    }

	    $value = $this->value;
	    if ($escapeHtml) {
	        $value = htmlspecialchars($value);
	    } else {
	        $value = addcslashes($value, '"');
	    }

	    $ini .= $this->key . ' = "' . $value . '"';

	    return $ini;
	}

}