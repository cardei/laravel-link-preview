<?php

namespace Cardei\LinkPreview\Parsers;

use Cardei\LinkPreview\Contracts\LinkInterface;
use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Contracts\ReaderInterface;
use Cardei\LinkPreview\Contracts\ParserInterface;
use Cardei\LinkPreview\Exceptions\ConnectionErrorException;
use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Readers\HttpReader;
use Cardei\LinkPreview\Models\HtmlPreview;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

/**
 * Class HtmlParser
 */
class HtmlParser extends BaseParser implements ParserInterface
{
    /**
     * Supported HTML tags
     *
     * @var array
     */
    private $tags = [
        'cover' => [
            ['selector' => 'meta[property="twitter:image"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:image"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="image"]', 'attribute' => 'content'],
        ],
        'title' => [
            ['selector' => 'meta[property="twitter:title"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:title"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="name"]', 'attribute' => 'content'],
            ['selector' => 'title']
        ],
        'description' => [
            ['selector' => 'meta[property="twitter:description"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:description"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="description"]', 'attribute' => 'content'],
            ['selector' => 'meta[name="description"]', 'attribute' => 'content'],
        ],
        'video' => [
            ['selector' => 'meta[property="twitter:player:stream"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:video"]', 'attribute' => 'content'],
        ],
        'videoType' => [
            ['selector' => 'meta[property="twitter:player:stream:content_type"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:video:type"]', 'attribute' => 'content'],
        ],
    ];

    /**
     * Smaller images will be ignored
     * @var int
     */
    private $imageMinimumWidth = 300;
    private $imageMinimumHeight = 300;

    /**
     * @param ReaderInterface $reader
     * @param PreviewInterface $preview
     */
    public function __construct(ReaderInterface $reader = null, PreviewInterface $preview = null)
    {
        $this->setReader($reader ?: new HttpReader());
        $this->setPreview($preview ?: new HtmlPreview());

        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug('H========================================= v2.0.41 ==========================================');
            Log::debug('HTML parser initialized');
            Log::debug('HTML reader: ' . get_class($this->getReader()));
            Log::debug('HTML preview: ' . get_class($this->getPreview()));
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'general';
    }

    /**
     * @inheritdoc
     */
    public function canParseLink(LinkInterface $link)
    {
        Log::debug('Inside canParseLink with URL: ' . $link->getUrl());
        return !filter_var($link->getUrl(), FILTER_VALIDATE_URL) === false;
    }

    /**
     * @inheritdoc
     */
    public function parseLink(LinkInterface $link)
    {
        try {
            Log::debug('Parsing HTML link: ' . $link->getUrl());

            $link = $this->readLink($link);

            if (!$link->isUp()) {
                throw new ConnectionErrorException();
            }

            if ($link->isHtml()) {
                $this->getPreview()->update($this->parseHtml($link));
            } else if ($link->isImage()) {
                $this->getPreview()->update($this->parseImage($link));
            }

            if (config('link-preview.enable_logging') && config('app.debug')) {
                Log::debug('Generated HTML preview for: ' . $link->getUrl());
            }

            return $this;

        } catch (\Exception $e) {
            if (config('link-preview.enable_logging') && config('app.debug')) {
                Log::debug('Error parsing HTML link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * @param LinkInterface $link
     * @return array
     */
    protected function parseImage(LinkInterface $link)
    {
        return [
            'cover' => $link->getEffectiveUrl(),
            'images' => [
                $link->getEffectiveUrl()
            ]
        ];
    }

       /**
     * Extract required data from html source
     * @param LinkInterface $link
     * @return array
     */
    protected function parseHtml(LinkInterface $link)
    {
        $images = [];

        try {
            $parser = new Crawler();
            $parser->addHtmlContent($link->getContent());

            // Parse all known tags
            foreach ($this->tags as $tag => $selectors) {
                foreach ($selectors as $selector) {
                    if ($parser->filter($selector['selector'])->count() > 0) {
                        if (isset($selector['attribute'])) {
                            ${$tag} = $parser->filter($selector['selector'])->first()->attr($selector['attribute']);
                        } else {
                            ${$tag} = $parser->filter($selector['selector'])->first()->text();
                        }
                        Log::debug("Parsed tag {$tag}: " . ${$tag});
                        break;
                    }
                }
                // Default is empty string
                if (!isset(${$tag})) ${$tag} = '';
            }

            // Parse all images on this page
            foreach ($parser->filter('img') as $image) {
                if (!$image->hasAttribute('src')) continue;
                if (filter_var($image->getAttribute('src'), FILTER_VALIDATE_URL) === false) continue;

                // Check image dimensions
                if ($image->hasAttribute('width') && $image->getAttribute('width') < $this->imageMinimumWidth) continue;
                if ($image->hasAttribute('height') && $image->getAttribute('height') < $this->imageMinimumHeight) continue;

                $images[] = $image->getAttribute('src');
            }

            Log::debug('Images parsed: ' . implode(', ', $images));

        } catch (\InvalidArgumentException $e) {
            if (config('link-preview.enable_logging') && config('app.debug')) {
                Log::debug('Error parsing HTML content for: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            }
        }

        $images = array_unique($images);

        if (!isset($cover) && count($images)) $cover = $images[0];

        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug('🚩 videoType : ' . $videoType);
            Log::debug('🚩 video : ' . $video);
        }

        return compact('cover', 'title', 'description', 'images', 'video', 'videoType');
    }
}
