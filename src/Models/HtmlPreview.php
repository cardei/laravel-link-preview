<?php

namespace Cardei\LinkPreview\Models;

use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Traits\HasExportableFields;
use Cardei\LinkPreview\Traits\HasImportableFields;

class HtmlPreview implements PreviewInterface
{
    use HasExportableFields;
    use HasImportableFields;

    /**
     * @var string $description Link description
     */
    private $description;

    /**
     * @var string $cover Cover image (usually chosen by webmaster)
     */
    private $cover;

    /**
     * @var array Images found while parsing the link
     */
    private $images = [];

    /**
     * @var string $title Link title
     */
    private $title;

    /**
     * @var string $video Video for the page (chosen by the webmaster)
     */
    private $video;

    /**
     * @var string $videoType If there is a video, what type it is
     */
    private $videoType;

    /**
     * Fields exposed
     * @var array
     */
    private $fields = [
        'cover',
        'images',
        'title',
        'description',
        'video',
        'videoType',
    ];

    /**
     * Set the description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the cover image URL
     *
     * @param string $cover
     * @return $this
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
        return $this;
    }

    /**
     * Get the cover image URL
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set the images array
     *
     * @param array $images
     * @return $this
     */
    public function setImages(array $images)
    {
        $this->images = $images;
        return $this;
    }

    /**
     * Get the images array
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set the title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the video URL
     *
     * @param string $video
     * @return $this
     */
    public function setVideo($video)
    {
        $this->video = $video;
        return $this;
    }

    /**
     * Get the video URL
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set the video type
     *
     * @param string $videoType
     * @return $this
     */
    public function setVideoType($videoType)
    {
        $this->videoType = $videoType;
        return $this;
    }

    /**
     * Get the video type
     *
     * @return string
     */
    public function getVideoType()
    {
        return $this->videoType;
    }

    /**
     * Get the fields exposed for export or import
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
