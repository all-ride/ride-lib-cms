<?php

namespace pallo\library\cms\content\mapper\io;

/**
 * Interface to load content mappers
 */
interface ContentMapperIO {

    /**
     * Gets a content mapper
     * @param string $type Name of the content type
     * @return pallo\library\cms\content\mapper\ContentMapper|null
     */
    public function getContentMapper($type);

    /**
     * Gets the available content mappers
     * @return array Array with ContentMapper objects
     * @see pallo\library\cms\content\mapper\ContentMapper
     */
    public function getContentMappers();

}