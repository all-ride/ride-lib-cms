<?php

namespace ride\library\cms;

use ride\library\cms\exception\CmsException;
use ride\library\cms\layout\LayoutModel;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\Node;
use ride\library\cms\node\SiteNode;
use ride\library\cms\theme\ThemeModel;
use ride\library\cms\widget\WidgetModel;
use ride\library\security\SecurityManager;

/**
 * Facade for the CMS library
 */
class Cms {

    /**
     * Constructs a new CMS facade
     * @param \ride\library\cms\node\NodeModel $nodeModel
     * @param \ride\library\cms\theme\ThemeModel $themeModel
     * @param \ride\library\cms\layout\LayoutModel $layoutModel
     * @param \ride\library\cms\widget\WidgetModel $widgetModel
     * @param \ride\library\security\SecurityManager $securityManager
     * @return null
     */
    public function __construct(NodeModel $nodeModel, ThemeModel $themeModel, LayoutModel $layoutModel, WidgetModel $widgetModel, SecurityManager $securityManager) {
        $this->nodeModel = $nodeModel;
        $this->themeModel = $themeModel;
        $this->layoutModel = $layoutModel;
        $this->widgetModel = $widgetModel;
        $this->securityManager = $securityManager;
    }

    /**
     * Gets the node model
     * @return \ride\library\cms\node\NodeModel
     */
    public function getNodeModel() {
        return $this->nodeModel;
    }

    /**
     * Gets the theme model
     * @return \ride\library\cms\theme\ThemeModel
     */
    public function getThemeModel() {
        return $this->themeModel;
    }

    /**
     * Gets the layout model
     * @return \ride\library\cms\layout\LayoutModel
     */
    public function getLayoutModel() {
        return $this->layoutModel;
    }

    /**
     * Gets the widget model
     * @return \ride\library\cms\widget\WidgetModel
     */
    public function getWidgetModel() {
        return $this->widgetModel;
    }

    /**
     * Gets the instance of the security manager
     * @return \ride\library\security\SecurityManager
     */
    public function getSecurityManager() {
        return $this->securityManager;
    }

    /**
     * Gets the name of the default revision
     * @return string
     */
    public function getDefaultRevision() {
        return $this->nodeModel->getDefaultRevision();
    }

    /**
     * Gets the name of the draft revision
     * @return string
     */
    public function getDraftRevision() {
        return $this->nodeModel->getDraftRevision();
    }

    /**
     * Gets a list of the locales
     * @return array Array with the code of the locale as key and value
     */
    public function getLocales() {
        return array('en' => 'en');
    }

    /**
     * Gets the available themes
     * @return array Array with Theme instances
     */
    public function getThemes() {
        return $this->themeModel->getThemes();
    }

    /**
     * Gets a theme
     * @param string $theme Machine name of the theme
     * @return \ride\library\cms\theme\Theme
     */
    public function getTheme($theme) {
        return $this->themeModel->getTheme($theme);
    }

    /**
     * Gets the available layouts
     * @return array Array with Layout instances
     */
    public function getLayouts() {
        return $this->layoutModel->getLayouts();
    }

    /**
     * Gets a layout
     * @param string $layout Machine name of the layout
     * @return \ride\library\cms\layout\Layout
     */
    public function getLayout($layout) {
        return $this->layoutModel->getLayout($layout);
    }

    /**
     * Gets the available widgets
     * @return array Array with Widget instances
     */
    public function getWidgets() {
        return $this->widgetModel->getWidgets();
    }

    /**
     * Gets a widget
     * @param string $widget Machine name of the widget
     * @return \ride\library\cms\widget\Widget
     */
    public function getWidget($widget) {
        return $this->widgetModel->getWidget($widget);
    }

    /**
     * Gets the available sites
     * @return array Array with SiteNode instances
     */
    public function getSites() {
        return $this->nodeModel->getSites();
    }

    /**
     * Gets the current site based on the URL
     * @param string $baseUrl Base URL to get a site for
     * @param string locale Resolved locale will be store in this variable
     * @return SiteNode|null
     */
    public function getCurrentSite($baseUrl, &$locale = null) {
        return $this->nodeModel->getCurrentSite($baseUrl, $locale);
    }

    /**
     * Creates a new node
     * @param string $type
     * @param \ride\library\cms\node\Node $parent
     * @return \ride\library\cms\node\Node
     */
    public function createNode($type, Node $parent = null) {
        return $this->nodeModel->createNode($type, $parent);
    }

    /**
     * Gets a node
     * @param string $siteId Id of the site
     * @param string $revision Name of the revision
     * @param string $nodeId Id of the node
     * @param boolean $children Set to true to lookup the children of the node
     * @param boolean|integer $depth Number of children levels to fetch, false
     * to fetch all child levels
     * @return \ride\library\cms\node\Node
     * @throws \ride\library\cms\exception\NodeNotFoundException when the
     * requested node could not be found
     */
    public function getNode($siteId, $revision, $nodeId, $type = null, $children = false, $depth = false) {
        return $this->nodeModel->getNode($siteId, $revision, $nodeId, $type, $children, $depth);
    }

    /**
     * Validates a node
     * @param \ride\library\cms\node\Node $node The node to validate
     * @return null
     * @throws \ride\library\validation\exception\ValidationException when the
     * node is invalid
     */
    public function validateNode(Node $node) {
        $this->nodeModel->validateNode($node);
    }

    /**
     * Saves a node to the data store
     * @param \ride\library\cms\node\Node $node Node to save
     * @param string $description Description for the save action
     * @return null
     */
    public function saveNode(Node $node, $description) {
        $this->nodeModel->setNode($node, $description);
    }

    /**
     * Clones a node
     * @param \ride\library\cms\node\Node $node Node to clone
     * @param boolean $recursive Flag to see if child nodes should be cloned
     * @return \ride\library\cms\node\Node Instance of the clone
     */
    public function cloneNode(Node $node, $recursive = true) {
        return $this->nodeModel->cloneNode($node, $recursive);
    }

    /**
     * Reorder the nodes of a site
     * @param \ride\library\cms\node\Node $node Parent node of the order array
     * @param array $order Array with the node id as key and the number of
     * children as value
     * @param string $locale Code of the locale
     * @return null
     */
    public function orderNodes(Node $node, array $order, $locale) {
        $this->nodeModel->orderNodes($node->getRootNodeId(), $node->getRevision(), $node->getId(), $order, $locale);
    }

    /**
     * Removes a node from the data store
     * @param \ride\library\cms\node\Node $node Node to remove
     * @param boolean $recursive Flag to see if child nodes should be removed
     * @return null
     */
    public function removeNode(Node $node, $recursive) {
        $this->nodeModel->removeNode($node, $recursive);
    }

    /**
     * Publishes a node
     * @param \ride\library\cms\node\Node $node Node to publish
     * @param string $revision Name of the destination revision
     * @param boolean $recursive Flag to see if children should be published
     * @return null
     */
    public function publishNode(Node $node, $revision = null, $recursive = true) {
        $this->nodeModel->publishNode($node, $revision, $recursive);
    }

    /**
     * Gets the ndoes of the trash
     * @param string $siteId Id of the site
     * @return array Array with TrashNode instances
     */
    public function getTrashNodes($site) {
        return $this->nodeModel->getTrashNodes($site);
    }

    /**
     * Restores the provided trash nodes
     * @param \ride\library\cms\node\SiteNode $site Site to manipulate
     * @param array $trashNodes Array with TrashNode instances
     * @param string $destination Materialized path of the new parent
     * @return null
     */
    public function restoreTrashNodes(SiteNode $site, array $trashNodes, $destination) {
        $this->nodeModel->restoreTrashNodes($site->getId(), $site->getRevision(), $trashNodes, $destination);
    }

    /**
     * Resolves the provided site and node
     * @param string $site Id of the site, will become the site Node instance
     * @param string $revision Name of the revision
     * @param string $node Id of the node, if set will become the Node instance
     * @param string $type Expected node type
     * @param boolean|integer $children Flag to see if child nodes should be
     * fetched
     * @return boolean True when the node is succesfully resolved, false if
     * the node could not be found, the response code will be set to 404
     */
    public function resolveNode(&$site, $revision = null, &$node = null, $type = null, $children = false) {
        if ($revision == null) {
            $revision = $this->nodeModel->getDefaultRevision();
        }

        try {
            $site = $this->nodeModel->getSite($site, $revision);
            if ($node) {
                $node = $this->nodeModel->getNode($site->getId(), $revision, $node, $type, $children);
            }
        } catch (CmsException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Resolves the provided region
     * @param \ride\library\cms\node\Node $node
     * @param string $locale Code of the locale
     * @param string $region Machine name of the region
     * @param string $theme Machine name of the theme
     * @return boolean True when the region is available in the provided node,
     * false if the region is not available, the response code will be set to
     * 404
     */
    public function resolveRegion(Node $node, $locale, $region, &$theme = null) {
        try {
            $theme = $node->getTheme();
            $theme = $this->themeModel->getTheme($theme);

            if (!$theme->hasRegion($region)) {
                throw new CmsException();
            }
        } catch (CmsException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Gets the number of children levels for the provided node
     * @param \ride\library\cms\node\Node $node
     * @return integer
     */
    public function getChildrenLevels(Node $node) {
        return $this->nodeModel->getChildrenLevels($node);
    }

    /**
     * Gets a list of the available nodes
     * @param \ride\library\cms\node\Node $node Root node for the list
     * @param string $locale Code of the locale
     * @param boolean $includeRootNode Flag to see if the root node should be
     * included in the result
     * @param boolean $includeEmpty Flag to see if a empty value should be
     * included in the result
     * @param boolean $onlyFrontendNodes Flag to filter on frontend nodes
     * @return array Array with the node id as key and a string as value
     */
    public function getNodeList(Node $node, $locale, $includeRootNode = false, $includeEmpty = true, $onlyFrontendNodes = true) {
        $rootNode = $this->nodeModel->getNode($node->getRootNodeId(), $node->getRevision(), $node->getId(), null, true);

        $options = $this->nodeModel->getListFromNodes(array($rootNode), $locale, $onlyFrontendNodes);

        if ($includeRootNode) {
            $options = array($rootNode->getId() => '/' . $rootNode->getName($locale)) + $options;
        }

        if ($includeEmpty) {
            $options = array('' => '---') + $options;
        }

        return $options;
    }

    /**
     * Gets the node type manager
     * @return \ride\library\cms\node\type\NodeTypeManager
     */
    public function getNodeTypeManager() {
        return $this->nodeModel->getNodeTypeManager();
    }

    /**
     * Gets the node types
     * @return array Array with the name of the node type as key and a NodeType
     * instance as value
     */
    public function getNodeTypes() {
        return $this->getNodeTypeManager()->getNodeTypes();
    }

    /**
     * Gets the node type of the provided node
     * @param \ride\library\cms\node\Node $node Node to get the type from
     * @return \ride\library\cms\node\type\NodeType
     */
    public function getNodeType(Node $node) {
        $nodeTypeManager = $this->nodeModel->getNodeTypeManager();

        return $nodeTypeManager->getNodeType($node->getType());
    }

}
