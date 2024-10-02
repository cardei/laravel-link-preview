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
use Exception;

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
            Log::debug('YouTube parser initialized');
        }
    }

    public function __toString()
    {
        return 'youtube';
    }

    public function canParseLink(LinkInterface $link)
    {
        Log::debug('Inside canParseLink with URL: ' . $link->getUrl());
        return (preg_match(static::PATTERN, $link->getUrl()));
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

            // Verificar si está disponible la API Key de YouTube
            $youtubeApiKey = config('link-preview.youtube_api_key');
            if ($youtubeApiKey) {
                $apiData = $this->fetchVideoDataFromApi($videoId, $youtubeApiKey);
                // Si el API devuelve datos válidos, actualiza el preview
                if ($apiData) {
                    $this->getPreview()->setTitle($apiData['title']);
                    $this->getPreview()->setDescription($apiData['description']);
                    $this->getPreview()->setCover($apiData['cover']);
                }
            }

            // Establecer el iframe
            $this->getPreview()->setId($videoId)
                ->setEmbed(
                    '<iframe id="ytplayer" type="text/html" width="640" height="390" src="//www.youtube.com/embed/' . e($this->getPreview()->getId()) . '" frameborder="0"></iframe>'
                );

            Log::debug('Generated YouTube iframe HTML: ' . $this->getPreview()->getEmbed());

            return $this;

        } catch (Exception $e) {
            Log::error('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch video data from the YouTube API and return it as an array
     *
     * @param string $videoId
     * @param string $youtubeApiKey
     * @return array|null
     */
    protected function fetchVideoDataFromApi($videoId, $youtubeApiKey)
    {
        Log::debug('Fetching video data from YouTube API for ID: ' . $videoId);

        try {
            $client = new GuzzleClient();
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
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding YouTube API response: ' . json_last_error_msg());
            }

            Log::debug('YouTube API Full Response: ' . json_encode($videoData));

            if (isset($videoData['items'][0])) {
                $snippet = $videoData['items'][0]['snippet'];

                return [
                    'title' => $snippet['title'],
                    'description' => $snippet['description'],
                    'cover' => $snippet['thumbnails']['high']['url'] ?? null,
                ];
            } else {
                Log::debug('No video data found via YouTube API for ID: ' . $videoId);
                return null;
            }

        } catch (Exception $e) {
            Log::error('YouTube API request failed for ID: ' . $videoId . '. Error: ' . $e->getMessage());
            return null;
        }
    }
}
