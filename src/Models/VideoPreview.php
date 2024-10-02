<?php

namespace Cardei\LinkPreview\Models;

use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Traits\HasExportableFields;
use Cardei\LinkPreview\Traits\HasImportableFields;

/**
 * Class VideoPreview
 */
class VideoPreview implements PreviewInterface
{
    use HasExportableFields;
    use HasImportableFields;

    /**
     * @var string $embed Video embed code
     */
    private $embed;

    /**
     * @var string $video Url to video
     */
    private $video;

    /**
     * @var string $id Video identification code
     */
    private $id;

    /**
     * @var string $title Video title
     */
    private $title;

    /**
     * @var string $description Video description
     */
    private $description;

    /**
     * @var string $cover Video cover image
     */
    private $cover;

    /**
     * @var string $icon Website icon (optional)
     */
    private $icon;

    /**
     * @var string $author Video author (optional)
     */
    private $author;

    /**
     * @var string $keywords Video keywords (optional)
     */
    private $keywords;

    /**
     * @var string $videoType If there is a video, what type it is (optional)
     */
    private $videoType;

    /**
     * Fields exposed
     * @var array
     */
    private $fields = [
        'embed',
        'video',
        'id',
        'title',
        'description',
        'cover',
        'icon',
        'author',
        'keywords',
        'videoType',
    ];

    /**
     * Set the embed code
     *
     * @param string $embed
     * @return $this
     */
    public function setEmbed($embed)
    {
        $this->embed = $embed;
        return $this;
    }

    /**
     * Get the embed code
     *
     * @return string
     */
    public function getEmbed()
    {
        return $this->embed;
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
     * Set the video ID
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the video ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the video title
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
     * Get the video title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the video description
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
     * Get the video description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the video cover image URL
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
     * Get the video cover image URL
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set the video icon URL (optional)
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Get the video icon URL
     *
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the video author (optional)
     *
     * @param string $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get the video author
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set the video keywords (optional)
     *
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * Get the video keywords
     *
     * @return string|null
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set the video type (optional)
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
     * @return string|null
     */
    public function getVideoType()
    {
        return $this->videoType;
    }

    /**
     * Get all exposed fields for export or import
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
