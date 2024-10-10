<?php

namespace Cardei\LinkPreview\Parsers;

use GuzzleHttp\Client;
use Cardei\LinkPreview\Models\Link;
use Illuminate\Support\Facades\Log;
use Cardei\LinkPreview\Models\VideoPreview;

class YouTubeParser extends BaseParser
{
    public function canParseLink(Link $link): bool
    {
        return preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/', $link->url);
    }

    public function parseLink(Link $link): VideoPreview
    {

        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/', $link->url, $matches);
        $videoId = $matches[1] ?? null;

        if (!$videoId) {
            throw new \Exception('Invalid YouTube URL');
        }

        $apiKey = config('link-preview.youtube_api_key');
        $client = new Client();
        $response = $client->get("https://www.googleapis.com/youtube/v3/videos", [
            'query' => [
                'id' => $videoId,
                'key' => $apiKey,
                'part' => 'snippet,contentDetails'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $videoData = $data['items'][0]['snippet'] ?? null;

        if (!$videoData) {
            throw new \Exception('YouTube video data not found');
        }

        $preview = new VideoPreview();
        $preview->title = $videoData['title'] ?? null;
        $preview->description = $videoData['description'] ?? null;
        $preview->cover = $videoData['thumbnails']['high']['url'] ?? null;
        $preview->video = $videoData['thumbnails']['high']['url'] ?? null;
        $preview->videoType = 'text/html';
        $preview->embed = sprintf('<iframe width="560" height="315" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>', $videoId);
        
        return $preview;
    }
}
