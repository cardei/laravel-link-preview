<?php

namespace LinkPreview\Parsers;

use LinkPreview\Models\Link;
use LinkPreview\Models\Preview;

class GeneralParser extends BaseParser
{
    public function canParseLink(Link $link): bool
    {
        return !empty($link->getContent());
    }

    public function parseLink(Link $link): Preview
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($link->getContent());
        $title = $doc->getElementsByTagName('title')->item(0)->textContent ?? '';
        $description = '';
        $metas = $doc->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('name')) === 'description') {
                $description = $meta->getAttribute('content');
                break;
            }
        }

        $preview = new Preview();
        $preview->setTitle($title)->setDescription($description);

        return $preview;
    }
}