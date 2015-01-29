<?php

namespace ride\library\cms\node\io;

use ride\library\cms\expired\ExpiredRouteModel;
use ride\library\cms\exception\CmsException;
use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\type\SiteNodeType;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeProperty;
use ride\library\cms\node\SiteNode;
use ride\library\cms\node\TrashNode;
use ride\library\config\parser\JsonParser;
use ride\library\system\file\File;

use \Exception;

/**
 * jSon implementation of the NodeIO
 */
class JsonNodeIO extends AbstractFileNodeIO {


    /**
     * Instance of the json parser
     * @var \ride\library\config\parser\JsonParser
     */
    protected $jsonParser;

    /**
     * Constructs a new ini node IO
     * @param \ride\library\system\file\File $path Path for the data files
     * @param \ride\library\config\ConfigHelper $configHelper Instance of the
     * configuration helper
     * @param \ride\library\cms\expired\ExpiredRouteModel $expiredRouteModel
     * Instance of the expired route model
     * @return null
     */
    public function __construct(File $path, JsonParser $jsonParser, ExpiredRouteModel $expiredRouteModel) {
        parent::__construct($path, $expiredRouteModel);

        $this->jsonParser = $jsonParser;
    }

    /**
     * Reads the site and it's revisions from the provided directory
     * @param \ride\library\system\file\File $siteDirectory Directory of the site
     * @param string $defaultRevision Default revision of the site
     * @return \ride\library\cms\node\SiteNode
     * @throws \ride\library\cms\exception\CmsException when the site could not
     * be read
     */
    protected function readSite(File $siteDirectory, $defaultRevision) {
        $revision = null;
        $revisions = $this->readSiteRevisions($siteDirectory, $defaultRevision, $revision);

        $revisionFile = $siteDirectory->getChild($revision . '.json');
        if ($revisionFile->exists()) {
            try {
                $site = $this->readSiteRevision($revisionFile);
            } catch (Exception $exception) {
                throw new CmsException('Could not parse the JSON configuration from ' . $revisionFile->getName(), 0, $exception);
            }
        } else {
            throw new CmsException('No valid site in ' . $siteDirectory->getName());
        }

        $site->setRevisions($revisions);
        $site->setRevision($revision);

        return $site;
    }

    /**
     * Reads all the nodes from the data source
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array Array with Node objects
     */
    protected function readNodes($siteId, $revision) {
        $revisionFile = $this->path->getChild($siteId . '/' . $revision . '.json');
        if (!$revisionFile->exists()) {
            return array();
        }

        return $this->readSiteRevision($revisionFile);
    }

    protected function readSiteRevision(File $revisionFile) {
        $nodes = array();

        $json = $revisionFile->read();

        $nodesArray = $this->jsonParser->parseToPhp($json);
        foreach ($nodesArray as $nodeId => $nodeArray) {
            $nodes[$nodeId] = $this->getNodeFromArray($nodeArray);
        }

        return $nodes;
    }

}
