<?php

namespace Cardei\LinkPreview\Tests\Parser;

use Cardei\LinkPreview\Parsers\HtmlParser;
use Cardei\LinkPreview\Exceptions\MalformedUrlException;

class GeneralParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider urlProvider
     * @param string $url
     * @expectedException Cardei\LinkPreview\Exceptions\MalformedUrlException
     * @test
     */
    public function html_parser_can_see_if_a_link_is_bogus_and_throw_exception($url)
    {
        $linkMock = $this->getMock('Cardei\LinkPreview\Models\Link', null, [$url]);

        $parser = new HtmlParser();

        self::setExpectedExceptionFromAnnotation();
    }

    /**
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['http:/trololo'],
            ['github.com']
        ];
    }
}