<?php

namespace LinkPreview;

use LinkPreview\Models\Link;
use LinkPreview\Readers\HttpReader;
use LinkPreview\Parsers\YouTubeParser;
use LinkPreview\Parsers\GeneralParser;

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