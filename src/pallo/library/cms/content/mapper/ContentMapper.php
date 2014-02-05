<?php

namespace pallo\library\cms\content\mapper;

/**
 * Interface to map specific data to generic content
 */
interface ContentMapper {

    /**
     * Sets the URL of the document root to this mapper
     * @param string $baseUrl URL to the public directory
     * @return null
     */
    public function setBaseUrl($baseUrl);

    /**
     * Sets the URL of the base script to this mapper
     * @param string $baseScript URL to link to pages in this system
     * @return null
     */
    public function setBaseScript($baseScript);

    /**
     * Gets the title or name of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string Title or name of the content
     */
	public function getTitle($site, $locale, $data);

	/**
     * Gets the teaser of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string Teaser of the content
	 */
	public function getTeaser($site, $locale, $data);

	/**
     * Gets the URL to the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string URL to the detail of the content
	 */
	public function getUrl($site, $locale, $data);

	/**
     * Gets the image of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return string URL to the image of the content
	 */
	public function getImage($site, $locale, $data);

	/**
	 * Gets the date of the content
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
	 * @param mixed $data Data of the content
	 * @return integer Timestamp of the content
	 */
	public function getDate($site, $locale, $data);

	/**
     * Gets a generic content object of the data
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param mixed $data Data of the content
     * @return pallo\library\cms\content\Content Instance of a Content object
	 */
	public function getContent($site, $locale, $data);

}