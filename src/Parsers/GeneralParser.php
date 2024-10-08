<?php

namespace Cardei\LinkPreview\Parsers;

use Symfony\Component\DomCrawler\Crawler;
use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Models\Preview;

class GeneralParser extends BaseParser
{
    private $metas = [
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
    ];

    public function canParseLink(Link $link): bool
    {
        return true; // General parser can parse any link
    }

    public function parseLink(Link $link): Preview
    {
        $crawler = new Crawler($link->content);
        $preview = new Preview();

        foreach ($this->metas as $property => $selectors) {
            foreach ($selectors as $meta) {
                $node = $crawler->filter($meta['selector']);
                if ($node->count()) {
                    $preview->{$property} = $node->attr($meta['attribute']) ?? $node->text();
                    break;
                }
            }
        }

        return $preview;
    }
}

// OLD: 
// namespace Cardei\LinkPreview\Parsers;

// use Cardei\LinkPreview\Models\Link;
// use Cardei\LinkPreview\Models\Preview;

// class GeneralParser extends BaseParser
// {
//     public function canParseLink(Link $link): bool
//     {
//         return !empty($link->getContent());
//     }

//     public function parseLink(Link $link): Preview
//     {
//         $doc = new \DOMDocument();
//         @$doc->loadHTML($link->getContent());
//         $title = $doc->getElementsByTagName('title')->item(0)->textContent ?? '';
//         $description = '';
//         $metas = $doc->getElementsByTagName('meta');
//         foreach ($metas as $meta) {
//             if (strtolower($meta->getAttribute('name')) === 'description') {
//                 $description = $meta->getAttribute('content');
//                 break;
//             }
//         }

//         $preview = new Preview();
//         $preview->setTitle($title)->setDescription($description);

//         return $preview;
//     }
// }