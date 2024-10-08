<?php

namespace Cardei\LinkPreview\Parsers;

use Cardei\LinkPreview\Models\Link;
use Cardei\LinkPreview\Models\Preview;

abstract class BaseParser
{
    abstract public function canParseLink(Link $link): bool;
    abstract public function parseLink(Link $link): Preview;
}