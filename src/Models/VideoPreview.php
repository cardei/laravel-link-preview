<?php

namespace LinkPreview\Models;

class VideoPreview extends Preview
{
    protected $cover;
    protected $embed;

    public function setCover(string $cover): self
    {
        $this->cover = $cover;
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
            'cover' => $this->cover,
            'embed' => $this->embed,
        ]);
    }
}