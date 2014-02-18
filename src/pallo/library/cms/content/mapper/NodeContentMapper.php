<?php

namespace pallo\library\cms\content\mapper;

use pallo\library\cms\content\Content;
use pallo\library\cms\node\Node;

/**
 * Content mapper for the CMS nodes
 */
class NodeContentMapper extends AbstractContentMapper {

    /**
     * Gets a generic content object of the data
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return pallo\library\cms\content\Content Instance of a Content object
     */
    public function getContent($site, $locale, $data) {
        if (!$data instanceof Node) {
            $data = $this->nodeModel->getNode($data);
        }

        if ($data->getRootNodeId() != $site) {
            return null;
        }

        return new Content($data->getType() . 'Node', $data->getName($locale), $this->baseScript . $data->getRoute($locale), null, null, $data);
    }

}