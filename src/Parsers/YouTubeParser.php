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

    /**
     * @param ReaderInterface $reader
     * @param PreviewInterface $preview
     */
    public function __construct(ReaderInterface $reader = null, PreviewInterface $preview = null)
    {
        $this->setReader($reader ?: new HttpReader());
        $this->setPreview($preview ?: new VideoPreview());

        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug('YouTube parser initialized');
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'youtube';
    }

    /**
     * @inheritdoc
     */
    public function canParseLink(LinkInterface $link)
    {
        Log::debug('Inside canParseLink with URL: ' . $link->getUrl());
        return (preg_match(static::PATTERN, $link->getUrl()));
    }

    /**
     * @inheritdoc
     */
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

            // Check if YouTube API Key is set and fetch data if available
            $youtubeApiKey = config('link-preview.youtube_api_key');
            $videoDataFetched = false;

            if ($youtubeApiKey) {
                $videoDataFetched = $this->fetchVideoDataFromApi($videoId, $youtubeApiKey);
            }

            // Fallback to HTML parsing if API data is not available
            if (!$videoDataFetched) {
                $this->getPreview()->setId($videoId)
                    ->setEmbed('<iframe id="ytplayer" type="text/html" width="640" height="390" src="//www.youtube.com/embed/' . e($this->getPreview()->getId()) . '" frameborder="0"></iframe>');

                Log::debug('Fallback to HTML parsing: Generated YouTube iframe HTML: ' . $this->getPreview()->getEmbed());
            }

            return $this;

        } catch (\Exception $e) {
            Log::debug('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Fetch video data from the YouTube API and update the preview model
     *
     * @param string $videoId
     * @param string $youtubeApiKey
     * @return bool Whether the API call was successful
     */
    protected function fetchVideoDataFromApi($videoId, $youtubeApiKey)
    {
        Log::debug('Fetching video data from YouTube API for ID: ' . $videoId);

        $client = new GuzzleClient();

        try {
            $response = $client->request('GET', 'https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    '
