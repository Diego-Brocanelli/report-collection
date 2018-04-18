<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StylesDefaultTest extends TestCase
{
    private function filename($prefix)
    {
        return tempnam(sys_get_temp_dir(), $prefix . '-') . '.xls';
    }

    public function testWithoutHeader()
    {
        $file = __DIR__ . '/../Files/table.xls'; 

        $xls_file = tempnam(sys_get_temp_dir(), 'style-default-header-no-') . '.xls';

        $handle  = \ReportCollection::createFromFile($file);
        
        $handle->debug_mode = true;

        $body    = $handle->getStyles('body');

        $this->assertEquals($body['background-color-odd'], '#ffffff');
        $this->assertEquals($body['background-color-even'], '#f5f5f5');
        $this->assertEquals($body['border-color-inside'], '#eeeeee');
        $this->assertEquals($body['border-color-outside'], '#555555');
        $this->assertEquals($body['color'], '#555555');
        $this->assertEquals($body['border-style-inside'], 'thin');
        $this->assertEquals($body['border-style-outside'], 'thick');
        $this->assertEquals($body['line-height'], 25);

        $handle->save($this->filename($xls_file));

        dd($handle->getDebugInfo());

        $this->assertTrue(true);
    }

    public function testWithHeader()
    {
        $file = __DIR__ . '/../Files/table.xls'; 

        $xls_file = tempnam(sys_get_temp_dir(), 'style-default-header-yes-') . '.xls';

        $handle = \ReportCollection::createFromFile($file);

        $handle->addHeaderRow('<b>Report Collection</b>');
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>');
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');

        $body = $handle->getStyles('body');

        $this->assertEquals($body['background-color-odd'], '#ffffff');
        $this->assertEquals($body['background-color-even'], '#f5f5f5');
        $this->assertEquals($body['border-color-inside'], '#eeeeee');
        $this->assertEquals($body['border-color-outside'], '#555555');
        $this->assertEquals($body['color'], '#555555');
        $this->assertEquals($body['border-style-inside'], 'thin');
        $this->assertEquals($body['border-style-outside'], 'thick');
        $this->assertEquals($body['line-height'], 25);

        $header = $handle->getStyles('header');

        $this->assertEquals($header['background-color-odd'], '#555555');
        $this->assertEquals($header['background-color-even'], '#555555');
        $this->assertEquals($header['border-color-inside'], '#444444');
        $this->assertEquals($header['border-color-outside'], '#555555');
        $this->assertEquals($header['color'], '#ffffff');
        $this->assertEquals($header['border-style-inside'], 'thin');
        $this->assertEquals($header['border-style-outside'], 'thick');
        $this->assertEquals($header['line-height'], 25);


        $handle->save($this->filename($xls_file));

        $this->assertTrue(true);
    }
}
    