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
        // Ensure a fallback preview instance is always created
        $this->setReader($reader ?: new HttpReader());
        $this->setPreview($preview ?: new VideoPreview());

        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug('========================================== v2 HD 31 ==========================================');
            Log::debug('🤩 YouTube Parser Initialized.'); 
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
        Log::debug('Checking if YouTube Parser can parse link: ' . $link->getUrl());
        return preg_match(static::PATTERN, $link->getUrl());
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

            // Check if YouTube API Key is set
            $youtubeApiKey = config('link-preview.youtube_api_key');
            if ($youtubeApiKey && $this->fetchVideoDataFromApi($videoId, $youtubeApiKey)) {
                Log::debug("YouTube API Data fetched successfully.");
            } else {
                Log::debug("YouTube API Data fetch failed, proceeding with HTML fallback.");
                $this->parseHtmlFallback($link); // Fallback to HTML parsing
            }

            // Generate the iframe for the video
            $this->getPreview()->setId($videoId)
                ->setEmbed(
                    '<iframe id="ytplayer" type="text/html" width="640" height="390" src="' . e('//www.youtube.com/embed/'.$this->getPreview()->getId()) . '" frameborder="0"></iframe>'
                );

            Log::debug('Generated YouTube iframe HTML: ' . $this->getPreview()->getEmbed());

            return $this;

        } catch (\Exception $e) {
            Log::error('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Fetch video data from the YouTube API and update the preview model
     *
     * @param string $videoId
     * @param string $youtubeApiKey
     * @return bool  // Return whether the API fetch was successful or not
     */
    protected function fetchVideoDataFromApi($videoId, $youtubeApiKey)
    {
        Log::debug('⭕️ YOUTUBE Fetching video data from YouTube API for ID: ' . $videoId);

        $preview = $this->getPreview();  // Asigna el preview a una variable local
        if (!$preview) {
            Log::error('Error: No preview object available.');
            return false;  // Detener si no hay un objeto preview válido
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

            // Verificar que los elementos principales de la respuesta existen
            if (!empty($videoData['items']) && isset($videoData['items'][0]['snippet'])) {

                Log::debug('👍🏻 YouTube API Data found for ID: ' . $videoId);

                $snippet = $videoData['items'][0]['snippet'];

                Log::debug('👉🏻 YouTube API Snippet Data: ' . json_encode($snippet));

                Log::debug('$snippet["title"]' . $snippet['title']);
                Log::debug('$snippet["description"]' . $snippet['description']);
                Log::debug('$snippet["thumbnails"]["high"]["url"]' . $snippet['thumbnails']['high']['url']);

                Log::debug('-> Before updating preview object with YouTube API data:');

                try {
                    // Asegurarse de que el preview sigue siendo válido
                    if (!$preview) {
                        throw new \Exception("Preview object became invalid.");
                    }

                    // Actualización del preview con los datos de YouTube
                    $preview->setTitle($snippet['title'] ?? 'No title available');
                    $preview->setDescription($snippet['description'] ?? 'No description available');
                    $preview->setCover($snippet['thumbnails']['high']['url'] ?? '');

                    $preview->setId($videoId)
                        ->setEmbed(
                            '<iframe id="ytplayer" type="text/html" width="640" height="390" src="' . e('//www.youtube.com/embed/'.$preview->getId()) . '" frameborder="0"></iframe>'
                        );

                } catch (\Exception $p_error) {
                    Log::error('Error while accessing preview object data: ' . $p_error->getMessage());
                    throw $p_error;
                }

                Log::debug('-> YouTube API Data updated in preview object with data:');
                Log::debug('Verifying updated preview object data:');
                Log::debug('Title: ' . $preview->getTitle());
                Log::debug('Description: ' . $preview->getDescription());
                Log::debug('Cover: ' . $preview->getCover());

            } else {
                Log::debug('😡 No valid video data found via YouTube API for ID: ' . $videoId);
                return false; // Indicar fallo
            }

        } catch (RequestException $e) {
            // Manejo de errores HTTP específicos de Guzzle
            Log::error('🛑 Error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);

            if ($e->hasResponse()) {
                Log::debug('Error response: ' . $e->getResponse()->getBody()->getContents());
            }

            return false;  // Indicar fallo
        } catch (\Exception $e) {
            // Manejo de cualquier otra excepción
            Log::error('🛑 General error fetching YouTube API data for ID: ' . $videoId, ['error' => $e->getMessage()]);
            return false;  // Indicar fallo
        }

        return true;  // Indicar éxito
    }



    /**
     * Fallback to HTML parsing in case API fetch fails
     * @param LinkInterface $link
     */
    protected function parseHtmlFallback(LinkInterface $link)
    {
        // Add the logic for HTML fallback parsing here
        // For example, you could use the existing HtmlParser to extract basic metadata from the HTML source.
        Log::debug('Falling back to HTML parsing for YouTube video ID: ' . $link->getUrl());
        // You could invoke the HtmlParser or process the HTML tags manually
    }

}
