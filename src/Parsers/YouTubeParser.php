<?php

namespace Cardei\LinkPreview\Parsers;

use Cardei\LinkPreview\Contracts\LinkInterface;
use Cardei\LinkPreview\Contracts\ReaderInterface;
use Cardei\LinkPreview\Contracts\ParserInterface;
use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Models\VideoPreview;
use Cardei\LinkPreview\Readers\HttpReader;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Class YouTubeParser
 */
class YouTubeParser extends BaseParser implements ParserInterface
{
    const PATTERN = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

    public function __construct(ReaderInterface $reader = null, PreviewInterface $preview = null)
    {
        $this->setReader($reader ?: new HttpReader());
        $this->setPreview($preview ?: new VideoPreview());

        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug('🤩 YouTube Parser Initialized.');
        }
    }

    public function __toString()
    {
        return 'youtube';
    }

    public function canParseLink(LinkInterface $link)
    {
        Log::debug('Checking if YouTube Parser can parse link: ' . $link->getUrl());
        return preg_match(static::PATTERN, $link->getUrl());
    }

    public function parseLink(LinkInterface $link)
    {
        try {
            Log::debug('Parsing YouTube link: ' . $link->getUrl());

            preg_match(static::PATTERN, $link->getUrl(), $matches);

            if (!isset($matches[1])) {
                Log::debug('The YouTube URL is not valid: ' . $link->getUrl());
                return $this;
            }

            $videoId = $matches[1];
            Log::debug('YouTube video ID: ' . $videoId);

            $youtubeApiKey = config('link-preview.youtube_api_key');
            if ($youtubeApiKey && $this->fetchVideoDataFromApi($videoId, $youtubeApiKey)) {
                Log::debug("YouTube API Data fetched successfully.");
                return $this->generatePreviewData($videoId);
            } else {
                Log::debug("YouTube API Data fetch failed, proceeding with HTML fallback.");
                return $this->parseHtmlFallback($link);  // Fallback al HTML si falla la API
            }

        } catch (\Exception $e) {
            Log::error('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch video data from YouTube API and return parsed data as an array
     *
     * @param string $videoId
     * @param string $youtubeApiKey
     * @return bool|array  // Return whether the API fetch was successful or not
     */
    protected function fetchVideoDataFromApi($videoId, $youtubeApiKey)
    {
        Log::debug('Fetching video data from YouTube API for ID: ' . $videoId);

        $client = new GuzzleClient();

        try {
            $response = $client->request('GET', 'https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    'id' => $videoId,
                    'part' => 'snippet,contentDetails',
                    'key' => $youtubeApiKey,
                ],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0'
                ]
            ]);

            $videoData = json_decode($response->getBody(), true);
            Log::debug('YouTube API Full Response: ' . json_encode($videoData));

            if (!empty($videoData['items']) && isset($videoData['items'][0]['snippet'])) {
                Log::debug('YouTube API Data found for ID: ' . $videoId);
                return $videoData['items'][0]['snippet'];
            } else {
                Log::debug('No valid video data found via YouTube API for ID: ' . $videoId);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate preview data array from the fetched YouTube API data
     *
     * @param string $videoId
     * @return array
     */
    protected function generatePreviewData($videoId)
    {
        $snippet = $this->getPreview()->getPreviewData();

        return [
            'cover' => $snippet['thumbnails']['high']['url'] ?? '',
            'title' => $snippet['title'] ?? 'No title available',
            'description' => $snippet['description'] ?? 'No description available',
            'video' => 'https://www.youtube.com/watch?v=' . $videoId,
            'videoType' => 'text/html',
        ];
    }

    protected function parseHtmlFallback(LinkInterface $link)
    {
        Log::debug('Falling back to HTML parsing for YouTube video ID: ' . $link->getUrl());
        // Aquí procesas el HTML manualmente o utilizando algún parser de HTML
        return [
            'cover' => '',
            'title' => 'No title available',
            'description' => 'No description available',
            'video' => $link->getUrl(),
            'videoType' => 'text/html',
        ];
    }
}
