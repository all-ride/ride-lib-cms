<?php

namespace ride\library\cms\content\mapper;

use ride\library\cms\content\Content;
use ride\library\cms\node\Node;

/**
 * Content mapper for the CMS nodes
 */
class NodeContentMapper extends AbstractContentMapper {

    /**
     * Gets a generic content object of the data
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return \ride\library\cms\content\Content Instance of a Content object
     */
    public function getContent($site, $locale, $data) {
        if (!$data instanceof Node) {
            $data = $this->nodeModel->getNode($site, $this->nodeModel->getDefaultRevision(), $data);
        }

        if ($data->getRootNodeId() != $site) {
            return null;
        }

        return new Content($data->getType() . 'Node', $data->getName($locale), $data->getUrl($locale, $this->baseScript), $data->getDescription($locale), $data->getImage($locale), $data);
    }

}
