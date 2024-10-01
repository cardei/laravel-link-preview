<?php

namespace Cardei\LinkPreview\Parsers;

use Cardei\LinkPreview\Contracts\LinkInterface;
use Cardei\LinkPreview\Contracts\ReaderInterface;
use Cardei\LinkPreview\Contracts\ParserInterface;
use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Models\VideoPreview;
use Cardei\LinkPreview\Readers\HttpReader;
use Illuminate\Support\Facades\Log;



/**
 * Class YouTubeParser
 */
class YouTubeParser extends BaseParser implements ParserInterface
{
    // OLD:
    /**
     * Url validation pattern taken from http://stackoverflow.com/questions/2964678/jquery-youtube-url-validation-with-regex
     */
    // const PATTERN = '/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/';

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
                if (config('link-preview.enable_logging') && config('app.debug')) {
                    Log::debug('The YouTube URL is not valid: ' . $link->getUrl());
                }
                // Ignore exceptions for now
                // throw new \InvalidArgumentException('URL de YouTube no válida.');
            }

            $this->getPreview()
                ->setId($matches[1])
                ->setEmbed(
                    '<iframe id="ytplayer" type="text/html" width="640" height="390" src="' . e('//www.youtube.com/embed/'.$this->getPreview()->getId()) . '" frameborder="0"></iframe>'
                );


            // OLD CODE
            // preg_match(static::PATTERN, $link->getUrl(), $matches);

            // $this->getPreview()
            //     ->setId($matches[1])
            //     ->setEmbed(
            //         '<iframe id="ytplayer" type="text/html" width="640" height="390" src="' . e('//www.youtube.com/embed/'.$this->getPreview()->getId()) . '" frameborder="0"></iframe>'
            //     );
                
                // ->setEmbed(
                //     '<iframe id="ytplayer" type="text/html" width="640" height="390" src="//www.youtube.com/embed/'.$this->getPreview()->getId().'" frameborder="0"></iframe>'
                // );
    
            return $this;

        } catch (\Exception $e) {
            if (config('link-preview.enable_logging') && config('app.debug')) {
                Log::debug('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            }
            // Ignore exceptions for now
            // throw $e;
        }


    }
}