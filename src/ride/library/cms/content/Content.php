<?php

namespace ride\library\cms\content;

use ride\library\cms\exception\CmsException;

/**
 * Generic data container of a content type
 */
class Content {

    /**
     * Type of the content
     * @var string
     */
    public $type;

    /**
     * Title or name of the content
     * @var string
     */
    public $title;

    /**
     * Teaser of the content
     * @var string
     */
    public $teaser;

    /**
     * Url to the full content
     * @var string
     */
    public $url;

    /**
     * Url to the image of the content
     * @var string
     */
    public $image;

    /**
     * Date of the content
     * @var integer
     */
    public $date;

    /**
     * Data instance of the content
     * @var mixed
     */
    public $data;

    /**
     * Construct this data container
     * @param string $type Type of the content
     * @param string $title Title or name of the content
     * @param string $url URL to the full content
     * @param string $teaser Teaser of the content
     * @param string $image URL to the image of the content
     * @param string $date Date of the content
     * @param mixed $data Instance of the actual data
     * @return null
     */
    public function __construct($type, $title, $url = null, $teaser = null, $image = null, $date = null, $data = null) {
        $this->setType($type);
        $this->setTitle($title);

        $this->url = $url;
        $this->teaser = $teaser;
        $this->image = $image;
        $this->date = $date;
        $this->data = $data;
    }

    /**
     * Sets the type of this content
     * @param string $type
     * @return null
     * @throws ride\library\cms\exception\CmsException when the type is empty
     */
    private function setType($type) {
        if (!is_string($type) || !$type) {
            throw new CmsException('Could not initiate the content: provided type is empty');
        }

        $this->type = $type;
    }

    /**
     * Sets the title of this content
     * @param string $title
     * @return null
     * @throws ride\library\cms\exception\CmsException when the title is empty
     */
    private function setTitle($title) {
        if (!is_string($title) || !$title) {
            throw new CmsException('Could not initiate the content: provided title is empty');
        }

        $this->title = $title;
    }

}