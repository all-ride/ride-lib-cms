<?php

namespace pallo\library\cms\content\mapper;

use pallo\library\cms\node\NodeModel;

/**
 * Abstract implementation of a ContentMapper
 */
abstract class AbstractContentMapper implements ContentMapper {

    /**
     * Instance of the node model
     * @var pallo\library\cms\node\NodeModel
     */
    protected $nodeModel;

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
     * Constructs a new abstract content mapper
     * @param pallo\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @return null
     */
    public function __construct(NodeModel $nodeModel) {
        $this->nodeModel = $nodeModel;
    }

    /**
     * Sets the URL of the document root to this mapper
     * @param string $baseUrl URL to the public directory
     * @return null
     */
    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Sets the URL of the base script to this mapper
     * @param string $baseScript URL to link to pages on this system
     * @return null
     */
    public function setBaseScript($baseScript) {
        $this->baseScript = $baseScript;
    }

    /**
     * Gets the type of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string Type of the content
     */
    public function getType($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->type;
    }

    /**
     * Gets the title or name of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string Title or name of the content
     */
    public function getTitle($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->title;
    }

    /**
     * Gets the teaser of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string Teaser of the content
     */
    public function getTeaser($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->teaser;
    }

    /**
     * Gets the url to the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string URL to the detail of the content
     */
    public function getUrl($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->url;
    }

    /**
     * Gets the image of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string URL to the image of the content
     */
    public function getImage($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->image;
    }

    /**
     * Gets the date of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return integer Timestamp of the content
     */
    public function getDate($site, $locale, $data) {
        $content = $this->getContent($site, $locale, $data);

        return $content->date;
    }

}