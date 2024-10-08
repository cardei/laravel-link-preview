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
            Log::debug('Y========================================= v2 ==========================================');
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
            $client = new GuzzleClient();
            $response = $client->request('GET', 'https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    'id' => $videoId,
                    'key' => $youtubeApiKey,
                    'part' => 'snippet,contentDetails'
                ]
            ]);

            $videoData = json_decode($response->getBody()->getContents(), true);
            Log::debug('YouTube API Full Response: ' . json_encode($videoData));

            if (!empty($videoData['items']) && isset($videoData['items'][0]['snippet'])) {
                $snippet = $videoData['items'][0]['snippet'];
                Log::debug('👍🏻 YouTube API Data found for ID: ' . $videoId);

                // Actualiza el objeto preview directamente
                $preview = $this->getPreview();
                $preview->setTitle($snippet['title'] ?? 'No title available');
                $preview->setDescription($snippet['description'] ?? 'No description available');
                $preview->setCover($snippet['thumbnails']['high']['url'] ?? '');

                // Genera el iframe
                $preview->setId($videoId)
                    ->setEmbed(
                        '<iframe id="ytplayer" type="text/html" width="640" height="390" src="//www.youtube.com/embed/' . e($preview->getId()) . '" frameborder="0"></iframe>'
                    );

                Log::debug('Generated YouTube iframe HTML: ' . $preview->getEmbed());

                return $preview; // Devolvemos el preview de inmediato sin seguir con análisis HTML
            } else {
                Log::debug('😡 No valid video data found via YouTube API for ID: ' . $videoId);
                return false;
            }
        } catch (RequestException $e) {
            Log::error('🛑 Error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            return false;
        } catch (\Exception $e) {
            Log::error('🛑 General error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            return false;
        }
    }



    protected function fetchVideoDataFromApi($videoId, $youtubeApiKey)
    {
        Log::debug('⭕️ YOUTUBE Fetching video data from YouTube API for ID: ' . $videoId);

        $preview = $this->getPreview();
        if (!$preview) {
            Log::error('Error: No preview object available.');
            return false;
        }

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
                $snippet = $videoData['items'][0]['snippet'];
                Log::debug('👍🏻 YouTube API Data found for ID: ' . $videoId);

                // Actualiza el objeto preview directamente
                $preview->setTitle($snippet['title'] ?? 'No title available');
                $preview->setDescription($snippet['description'] ?? 'No description available');
                $preview->setCover($snippet['thumbnails']['high']['url'] ?? '');

                // Genera el iframe
                $preview->setId($videoId)
                    ->setEmbed(
                        '<iframe id="ytplayer" type="text/html" width="640" height="390" src="' . e('//www.youtube.com/embed/' . $preview->getId()) . '" frameborder="0"></iframe>'
                    );

                Log::debug('Generated YouTube iframe HTML: ' . $preview->getEmbed());

            } else {
                Log::debug('😡 No valid video data found via YouTube API for ID: ' . $videoId);
                return false;
            }

        } catch (RequestException $e) {
            Log::error('🛑 Error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            if ($e->hasResponse()) {
                Log::debug('Error response: ' . $e->getResponse()->getBody()->getContents());
            }
            return false;

        } catch (\Exception $e) {
            Log::error('🛑 General error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            return false;
        }

        return true;
    }

    protected function parseHtmlFallback(LinkInterface $link)
    {
        Log::debug('Falling back to HTML parsing for YouTube video ID: ' . $link->getUrl());
        // Aquí procesas el HTML manualmente o utilizando algún parser de HTML
    }
}
