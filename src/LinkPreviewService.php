<?php

namespace Cardei\LinkPreview;

use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Readers\HttpReader;
use Cardei\LinkPreview\Parsers\YouTubeParser;
use Cardei\LinkPreview\Parsers\GeneralParser;

class LinkPreviewService
{
    private $readers = [];
    private $parsers = [];

    public function __construct()
    {
        // Inicializa los readers y parsers
        $this->readers[] = new HttpReader();
        $this->parsers[] = new YouTubeParser();
        $this->parsers[] = new GeneralParser();
    }

    public function getPreview(string $url)
    {
        // Lee el enlace
        $link = new Link($url);
        foreach ($this->readers as $reader) {
            if ($reader->canRead($link)) {
                $reader->readLink($link);
            }
        }

        // Analiza con parsers
        foreach ($this->parsers as $parser) {
            if ($parser->canParseLink($link)) {
                return $parser->parseLink($link);
            }
        }

        return null;
    }
}