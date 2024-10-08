<?php

namespace Cardei\LinkPreview\Readers;

use Cardei\LinkPreview\Models\Link;
use GuzzleHttp\Client;

class HttpReader
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 100]);
    }

    public function canRead(Link $link): bool
    {
        return filter_var($link->getUrl(), FILTER_VALIDATE_URL) !== false;
    }

    public function readLink(Link $link): void
    {
        $response = $this->client->get($link->getUrl());
        $link->setContent($response->getBody()->getContents());
    }
}