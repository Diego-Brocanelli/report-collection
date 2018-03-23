<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StylesDefaultTest extends TestCase
{
    public function testStyles()
    {
        $file = __DIR__ . '/../Files/table.xls';

        $xls_style_default = tempnam(sys_get_temp_dir(), 'style_default-') . '.xls';
        $xls_style_custom  = tempnam(sys_get_temp_dir(), 'style_custom-') . '.xls';

        $handle = \ReportCollection::createFromFile($file);
        $default = $handle->getStyles();
        $handle->save($xls_style_default);


        $handle = \ReportCollection::createFromFile($file);
        $handle->setStyles('body', $default);
        $handle->save($xls_style_custom);

        $content_style_default = file_get_contents($xls_style_default);
        $content_style_custom  = file_get_contents($xls_style_custom);

        $this->assertEquals($content_style_default, $content_style_custom);

        //dd($handle->debug['applyStyles']);

        // $handle->setStyles('header', [
        //     'color' => '#222222'
        // ]);

        // $default = $handle->getStyles();
        // $total   = count($default);

        // $header  = $handle->getStyles('header');
        
        // $this->assertCount($total, $header);
        // $this->assertNotEquals($default, $header);

        // // única diferença
        // unset($default['color']);
        // unset($header['color']);
        // $this->assertEquals($default, $header);
    }
}
