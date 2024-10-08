<?php

namespace Cardei\LinkPreview\Models;

class VideoPreview extends Preview
{
    public $title;
    public $description;
    public $cover;
    public $video;
    public $videoType;
    public $embed;


    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setCover(string $cover): self
    {
        $this->cover = $cover;
        return $this;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;
        return $this;
    }

    public function setVideoType(string $videoType): self
    {
        $this->videoType = $videoType;
        return $this;
    }

    public function setEmbed(string $embed): self
    {
        $this->embed = $embed;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'title' => $this->title,
            'description' => $this->description,
            'cover' => $this->cover,
            'video' => $this->video,
            'videoType' => $this->videoType,
            'embed' => $this->embed,
        ]);
    }
}