<?php

namespace LinkPreview\Parsers;

use LinkPreview\Models\Link;
use LinkPreview\Models\Preview;

abstract class BaseParser
{
    abstract public function canParseLink(Link $link): bool;
    abstract public function parseLink(Link $link): Preview;
}