<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TextParserTest extends TestCase
{
    public function testParsing()
    {
        $xls  = tempnam(sys_get_temp_dir(), 'text-parsing-') . '.xls';

        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $textA1 = '<b>Report Collection</b>';
        $handle->addHeaderRow($textA1);

        $textA2 = '<b>Autor</b>: Ricardo Pereira <u>Dias</u>';
        $handle->addHeaderRow($textA2);

        $textA3 = '<i>Linguagem</i>: PHP não é <s>HTML</s>';
        $handle->addHeaderRow($textA3);

        $handle->save($xls);

        $debug = $handle->getDebugInfo()['styles']['header'];
        $this->assertEquals($debug['A1']['text-content'], strip_tags($textA1));
        $this->assertEquals($debug['A2']['text-content'], strip_tags($textA2));
        $this->assertEquals($debug['A3']['text-content'], strip_tags($textA3));
    }
}
