<?php

namespace ride\library\cms\node\io;

use ride\library\cms\node\NodeModel;
use ride\library\cms\node\Node;
use ride\library\cms\node\SiteNode;
use ride\library\cms\node\TrashNode;
use ride\library\system\file\File;

/**
 * Cache IO for another NodeIO. This IO will get the nodes from the wrapped IO
 * and generate a PHP script to include. When the generated PHP script exists,
 * this will be used to read the nodes.
 */
class CacheNodeIO extends AbstractNodeIO {

    /**
     * NodeIO which is cached by this instance
     * @var NodeIO
     */
    private $io;

    /**
     * File to write the cache to
     * @var \ride\library\system\file\File
     */
    private $file;

    /**
     * Constructs a new cached NodeIO
     * @param \ride\library\cms\node\io\NodeIO $io NodeIO which needs a cache
     * @param \ride\library\system\file\File $file File for the cache
     * @return null
     */
    public function __construct(NodeIO $io, File $file) {
        $this->io = $io;
        $this->setFile($file);
    }

    /**
     * Destruction of the cached NodeIO
     * @return null
     */
    public function __destruct() {
        if (isset($this->needsClear) && $this->needsClear) {
            $this->clearCache();
        }

        if (isset($this->needsWrite) && $this->needsWrite) {
            $this->writeCache();
        }
    }

    /**
     * Sets the instance of the node model
     * @param \ride\library\cms\node\NodeModel $nodeModel
     * @return null
     */
    public function setNodeModel(NodeModel $nodeModel) {
        parent::setNodeModel($nodeModel);

        $this->io->setNodeModel($nodeModel);
    }

    /**
     * Sets the file for the generated code
     * @param \ride\library\system\file\File $file File to generate the code in
     * @return null
     */
    public function setFile(File $file) {
        $this->file = $file;
    }

    /**
     * Gets the file for the generated code
     * @return \ride\library\system\file\File File to generate the code in
     * @return null
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Reads all the sites from the data source
     * @return array Array with the id of the site as key and the SiteNode as
     * value
     */
    protected function readSites() {
        if ($this->sites !== null) {
            return $this->sites;
        }

        $this->readCache();

        return $this->sites;
    }

    /**
     * Reads all the nodes from the data source
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @return array Array with the id of the node as key and the Node as value
     */
    protected function readNodes($siteId, $revision) {
        if (isset($this->nodes[$siteId][$revision])) {
            return $this->nodes[$siteId][$revision];
        }

        $this->readCache();

        if (isset($this->nodes[$siteId][$revision])) {
            return $this->nodes[$siteId][$revision];
        }

        return array();
    }

    /**
     * Reads the sites and nodes from the cache, if the cache is not available,
     * populate the cache
     * @return null
     */
    protected function readCache() {
        if ($this->file->exists()) {
            include $this->file->getPath();

            return;
        }

        $this->loadNodes();

        $this->needsWrite = true;
    }

    /**
     * Loads the nodes from the wrapped IO
     * @return null
     */
    protected function loadNodes() {
        $this->nodes = array();

        $this->sites = $this->io->readSites();
        foreach ($this->sites as $siteId => $site) {
            $this->nodes[$siteId] = array();

            $revisions = $site->getRevisions();
            foreach ($revisions as $revision) {
                $this->nodes[$siteId][$revision] = $this->io->readNodes($siteId, $revision);
            }
        }
    }

    /**
     * Writes the sites and nodes to the cache
     * @return null
     */
    protected function writeCache() {
        if (!$this->sites || !$this->nodes) {
            $this->needsClear = true;

            return;
        }

        // generate the PHP code for the obtained nodes
        $php = $this->generatePhp($this->sites, $this->nodes);

        // make sure the parent directory of the script exists
        $parent = $this->file->getParent();
        $parent->create();

        // write the PHP code to file
        $this->file->write($php);

        if (isset($this->needsWrite)) {
            unset($this->needsWrite);
        }
    }

    /**
     * Writes the provided node to the data source
     * @param \ride\library\cms\node\Node $node Node to write
     * @return null
     */
    protected function writeNode(Node $node) {
        $this->io->writeNode($node);

        if ($this->nodes !== null) {
            $this->nodes[$node->getRootNodeId()][$node->getRevision()][$node->getId()] = $node;
        }

        $this->needsClear = true;
    }

    /**
     * Deletes the provided node to the data source
     * @param \ride\library\cms\node\Node $node Node to delete
     * @return null
     */
    protected function deleteNode(Node $node) {
        $this->io->deleteNode($node);

        if (isset($this->nodes[$node->getRootNodeId()][$node->getRevision()][$node->getId()])) {
            unset($this->nodes[$node->getRootNodeId()][$node->getRevision()][$node->getId()]);
        }

        $this->needsClear = true;
    }

    /**
     * Restores a node
     */
    protected function restoreTrashNode($siteId, $revision, TrashNode $trashNode, $newParent = null) {
        $this->io->restoreTrashNode($siteId, $revision, $trashNode, $newParent);
        $this->clearCache();
    }

    /**
     * Publishes a site to the provided revision
     * @param \ride\library\cms\node\Node $node
     * @param string $revision
     * @param boolean $recursive Flag to see if the node's children should be
     * published as well
     * @return null
     */
    public function publish(Node $node, $revision, $recursive) {
        $this->io->publish($node, $revision, $recursive);
        $this->clearCache();
    }

    /**
     * Clears the cache of this node IO
     * @return null
     */
    public function clearCache() {
        $this->nodes = null;

        if ($this->file->exists()) {
            $this->file->delete();
        }

        if (isset($this->needsClear)) {
            unset($this->needsClear);
        }
    }

    /**
     * Generates a PHP source file for the provided events
     * @param array $eventListeners
     * @return string
     */
    protected function generatePhp(array $sites, array $nodes) {
        $output = "<?php\n\n";
        $output .= "/*\n";
        $output .= " * This file is generated by ride\\library\\cms\\node\\io\\CacheNodeIO.\n";
        $output .= " */\n";
        $output .= "\n";
        $output .= 'use ride\\library\\cms\\node\\NodeProperty;' . "\n";
        $output .= "\n";
        $output .= '$this->sites = array();';
        $output .= "\n";
        $output .= '$this->nodes = array();';
        $output .= "\n";
        $output .= "\n";

        foreach ($nodes as $siteId => $revisions) {
            $output .= '$this->nodes["' . $siteId . '"] = array();' . "\n";
            foreach ($revisions as $revision => $nodes) {
                $output .= '$this->nodes["' . $siteId . '"]["' . $revision . '"] = array();' . "\n";
                foreach ($nodes as $node) {
                    $output .= "// node " . $node->getId() . "\n";

                    $output .= '$node = $this->nodeModel->createNode("' . $node->getType() . '");';
                    $output .= "\n";
                    $output .= '$node->setId("' . $node->getId() . '");';
                    $output .= "\n";
                    $output .= '$node->setDateModified(' . $node->getDateModified() . ');';
                    $output .= "\n";
                    if ($node->getParent()) {
                        $output .= '$node->setParent("' . $node->getParent() . '");';
                        $output .= "\n";
                    }
                    if ($node->getOrderIndex() !== null && $node->getOrderIndex() !== '') {
                        $output .= '$node->setOrderIndex(' . $node->getOrderIndex() . ');';
                        $output .= "\n";
                    }

                    if ($node instanceof SiteNode) {
                        $output .= '$node->setWidgetIdOffset(' . $node->getWidgetIdOffset() . ');';
                        $output .= "\n";
                        $output .= '$node->setAvailableWidgets(' . var_export($node->getAvailableWidgets(), true) . ');';
                        $output .= "\n";
                        $output .= '$node->setRevisions(' . var_export($node->getRevisions(), true) . ');';
                        $output .= "\n";
                    }

                    $output .= '$node->setRevision("' . $node->getRevision() . '");';
                    $output .= "\n";

                    $output .= '$node->setProperties(array(' . "\n";
                    $nodeProperties = $node->getProperties();
                    ksort($nodeProperties);
                    foreach ($nodeProperties as $key => $nodeProperty) {
                        $output .= '    "' . $key . '" => new NodeProperty("' . $key . '", ' . var_export($nodeProperty->getValue(), true) . ', ' . var_export($nodeProperty->getInherit(), true) . '),' . "\n";
                    }
                    $output .= '));';
                    $output .= "\n";
                    $output .= "\n";
                    $output .= '$this->nodes["' . $siteId . '"]["' . $revision . '"]["' . $node->getId() . '"] = $node;';
                    $output .= "\n";
                    $output .= "\n";
                }

                $output .= '// set the parent node instances
foreach ($this->nodes["' . $siteId . '"]["' . $revision . '"] as $node) {
    $parentId = $node->getParentNodeId();
    if (!$parentId) {
        continue;
    }

    if (isset($this->nodes["' . $siteId . '"]["' . $revision . '"][$parentId])) {
        $node->setParentNode($this->nodes["' . $siteId . '"]["' . $revision . '"][$parentId]);
    } else {
        $rootId = $node->getRootNodeId();
        if (isset($this->nodes["' . $siteId . '"]["' . $revision . '"][$rootId])) {
            $node->setParentNode($this->nodes["' . $siteId . '"]["' . $revision . '"][$rootId]);
        }
    }
}

';
            }
        }

        foreach ($sites as $siteId => $site) {
            $output .= '$this->sites["' . $siteId . '"] = $this->nodes["' . $siteId . '"]["' . $site->getRevision() . '"]["' . $siteId . '"];' . "\n";
        }

        return $output;
    }

}
