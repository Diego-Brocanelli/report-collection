<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TextParserTest extends TestCase
{
    public function testParsing()
    {
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $text = '<b>Report Collection</b>';
        $handle->parseHtmlText($text);
        $this->assertEquals($handle->debug['parseHtmlText'], strip_tags($text));

        $text = '<b>Autor</b>: Ricardo Pereira <u>Dias</u>';
        $handle->parseHtmlText($text);
        $this->assertEquals($handle->debug['parseHtmlText'], strip_tags($text));

        $text = '<i>Linguagem</i>: PHP não é <s>HTML</s>';
        $handle->parseHtmlText($text);
        $this->assertEquals($handle->debug['parseHtmlText'], strip_tags($text));
    }
}
