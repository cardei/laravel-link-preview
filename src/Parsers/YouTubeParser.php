<?php

namespace Cardei\LinkPreview\Parsers;

use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Models\VideoPreview;
use GuzzleHttp\Client;

class YouTubeParser extends BaseParser
{
    const PATTERN = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/.*v=|youtu\.be\/)([a-zA-Z0-9_-]+)/';

    public function canParseLink(Link $link): bool
    {
        return preg_match(self::PATTERN, $link->getUrl());
    }

    public function parseLink(Link $link): VideoPreview
    {
        preg_match(self::PATTERN, $link->getUrl(), $matches);
        $videoId = $matches[1] ?? null;

        if (!$videoId) {
            throw new \Exception('Video ID not found.');
        }

        $client = new Client();
        $response = $client->get('https://www.googleapis.com/youtube/v3/videos', [
            'query' => [
                'id' => $videoId,
                'part' => 'snippet,contentDetails',
                'key' => 'YOUR_YOUTUBE_API_KEY',
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        if (empty($data['items'])) {
            throw new \Exception('No video data found.');
        }

        $snippet = $data['items'][0]['snippet'];
        $preview = new VideoPreview();
        $preview->setTitle($snippet['title'] ?? '')
                ->setDescription($snippet['description'] ?? '')
                ->setCover($snippet['thumbnails']['high']['url'] ?? '')
                ->setEmbed('<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>');

        return $preview;
    }
}