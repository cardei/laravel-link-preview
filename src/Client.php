<?php

namespace Cardei\LinkPreview;

use Cardei\LinkPreview\Contracts\ParserInterface;
use Cardei\LinkPreview\Contracts\PreviewInterface;
use Cardei\LinkPreview\Parsers\HtmlParser;
use Cardei\LinkPreview\Parsers\YouTubeParser;
use Cardei\LinkPreview\Parsers\VimeoParser;
use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Exceptions\UnknownParserException;
use Illuminate\Support\Facades\Log;


class Client
{
    /**
     * @var ParserInterface[]
     */
    private $parsers = [];

    /**
     * @var Link $link
     */
    private $link;

    /**
     * @param string $url Request address
     */
    public function __construct($url = null)
    {
        if ($url) $this->setUrl($url);
        $this->addDefaultParsers();
    }

    /**
     * Try to get previews from as many parsers as possible
     * @return PreviewInterface[]
     */
    public function getPreviews()
    {
        $parsed = [];

        foreach ($this->getParsers() as $name => $parser) {
            Log::debug("Attempting to parse with parser: " . $name);
            if ($parser->canParseLink($this->link)) {
                Log::debug("Parser $name can parse the link: " . $this->link->getUrl());
                $parsed[$name] = $parser->parseLink($this->link)->getPreview();
            } else {
                Log::debug("Parser $name cannot parse the link: " . $this->link->getUrl());
            }
        }

        return $parsed;
    }


    /**
     * Get a preview from a single parser
     * @param string $parserId
     * @throws UnknownParserException
     * @return PreviewInterface|boolean
     */
    public function getPreview($parserId)
    {
        if (array_key_exists($parserId, $this->getParsers())) {
            $parser = $this->getParsers()[$parserId];
        } else throw new UnknownParserException();

        return $parser->parseLink($this->link)->getPreview();
    }

    /**
     * Add parser to the beginning of parsers list
     *
     * @param ParserInterface $parser
     * @return $this
     */
    public function addParser(ParserInterface $parser)
    {
        $this->parsers = [(string) $parser => $parser] + $this->parsers;

        return $this;
    }

    /**
     * @param $id
     * @return bool|ParserInterface
     */
    public function getParser($id)
    {
        return isset($this->parsers[$id]) ? $this->parsers[$id] : false;
    }

    /**
     * Get parsers
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * Set parsers
     * @param ParserInterface[] $parsers
     * @return $this
     */
    public function setParsers($parsers)
    {
        $this->parsers = $parsers;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return (!empty($this->link->getEffectiveUrl())) ? $this->link->getEffectiveUrl() : $this->link->getUrl();
    }

    /**
     * Set target url
     *
     * @param string $url Website url to parse
     * @return $this
     */
    public function setUrl($url)
    {
        $this->link = new Link($url);

        return $this;
    }

    /**
     * Remove parser from parsers list
     *
     * @param string $name Parser name
     * @return $this
     */
    public function removeParser($name)
    {
        if (in_array($name, $this->parsers, false)) {
            unset($this->parsers[$name]);
        }

        return $this;
    }

    /**
     * Add default parsers
     * @return void
     */
    protected function addDefaultParsers()
    {
        $this->addParser(new HtmlParser());
        $this->addParser(new YouTubeParser());
        $this->addParser(new VimeoParser());
      
        if (config('link-preview.enable_logging') && config('app.debug')) {
            Log::debug("Default parsers added: " . implode(", ", array_keys($this->getParsers())));
        }
    }
}