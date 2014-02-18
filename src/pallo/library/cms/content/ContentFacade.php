<?php

namespace pallo\library\cms\content;

use pallo\library\cms\content\mapper\io\ContentMapperIO;
use pallo\library\cms\content\mapper\ContentMapper;
use pallo\library\cms\exception\CmsException;

/**
 * Facade to the generic content
 */
class ContentFacade {

    /**
     * URL to the document root
     * @var string
     */
    protected $baseUrl;

    /**
     * URL to the base script
     * @var string
     */
    protected $baseScript;

	/**
	 * Registered IO implementations for loading content mappers
	 * @var array
	 */
	protected $io;

	/**
	 * Registered content mappers
	 * @var array
	 */
	protected $mappers;

	/**
	 * Constructs a new content facade
	 * @return null
	 */
	public function __construct($baseUrl, $baseScript) {
	    $this->baseUrl = $baseUrl;
	    $this->baseScript = $baseScript;

	    $this->io = array();
	    $this->mappers = array();
	}

	/**
     * Gets the mapper for a content type
     * @param string $type Name of the content type
     * @return pallo\library\ms\content\mapper\ContentMapper Mapper for the
     * content type
     * @throws pallo\library\cms\exception\CmsException when no mapper could be
     * found
	 */
	public function getContentMapper($type) {
        if (!$type || !is_string($type)) {
            throw new CmsException('Could not get content mapper: provided type is empty or not a string');
        }

        if (isset($this->mappers[$type])) {
            return $this->mappers[$type];
        }

        foreach ($this->io as $io) {
            $mapper = $io->getContentMapper($type);
            if (!$mapper) {
                continue;
            }

            $mapper->setBaseUrl($this->baseUrl);
            $mapper->setBaseScript($this->baseScript);

            $this->mappers[$type] = $mapper;

            return $mapper;
        }

        throw new CmsException('Could not get content mapper for ' . $type . ': no content mapper set for this type');
    }

	/**
	 * Gets all the available mappers
	 * @return
	 */
	public function getContentMappers() {
	    $mappers = array();

	    foreach ($this->io as $io) {
	        $ioMappers = $io->getContentMappers();
	        foreach ($ioMappers as $type => $mapper) {
	            if (isset($mappers[$type])) {
	                continue;
	            }

	            $mapper->setBaseUrl($this->baseUrl);
	            $mapper->setBaseScript($this->baseScript);

	            $mappers[$type] = $mapper;
	        }
	    }

	    $this->mappers = $mappers;

	    return $this->mappers;
	}

	/**
	 * Registers a mapper for a content type
	 * @param string $type Name of the content type
	 * @param pallo\library\cms\content\mapper\ContentMapper $mapper Content
	 * mapper for the content type
	 * @return null
	 */
	public function addContentMapper($type, ContentMapper $mapper) {
		$this->mappers[$type] = $mapper;
	}

	/**
	 * Registers a IO implementation for loading content mappers
	 * @param pallo\library\cms\content\io\ContentMapperIO $io IO
	 * implementation for loading content mappers
	 * @return null
	 */
	public function addContentMapperIO(ContentMapperIO $io) {
		$this->io[] = $io;
	}

}