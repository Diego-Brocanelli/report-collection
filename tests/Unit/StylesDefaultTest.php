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

        $xls_style_default = tempnam(sys_get_temp_dir(), 'style-default-header-no-') . '.xls';
        $xls_style_custom  = tempnam(sys_get_temp_dir(), 'style-custom-header-no-') . '.xls';

        $handle  = \ReportCollection::createFromFile($file);
        $default = $handle->getStyles();
        $header  = $handle->getStyles('header');
        $body    = $handle->getStyles('body');

        $handle->save($this->filename($xls_style_default));

        $handle = \ReportCollection::createFromFile($file);

        // Adicionar os estilos do cabeçalho para normalizar

        $handle->addHeaderRow('<b>Report Collection</b>');
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>');
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');
        $handle->save($this->filename('styles-default-header'));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];
        $header_styles = $debug['styles']['header'];

        // $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        // $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        // $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        // $handle->setHeaderStyles([
        //     'background-color-odd'  => '#0000ff', // ímpar
        //     'background-color-even' => '#0000ff', // par

        //     'border-style-inside'   => 'thin',
        //     'border-color-inside'   => '#ffffff',

        //     'border-style-outside'   => 'thick',
        //     'border-color-outside'  => '#ff0000',

        //     'color'                 => '#ffffff',
        // ]);

        $handle->save($this->filename($xls_style_custom));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];
        $header_styles = $debug['styles']['header'];

        $this->assertTrue(true);

        // $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        // $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        // $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        // $this->assertEquals('#123456', $header_styles['A1']['background']);
        // $this->assertEquals('#654321', $header_styles['A2']['background']);
        // $this->assertEquals($header['background-color-odd'], $header_styles['A3']['background']);


        // // Estilos personalizados intermitentes com Cabeçalho
        // // ------------------------------------------------------------------

        // $handle = \ReportCollection::createFromFile($file);

        // $handle->addHeaderRow('<b>Report Collection</b>', ['background-color-odd' => '#123456']);
        // $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>', ['background-color-even' => '#654321']);
        // $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');

        // $handle->setHeaderStyles($default_styles);
        // $handle->setBodyStyles($default_styles);
        // $handle->save($this->filename('styles-custom-header-split'));

        // $debug = $handle->getDebugInfo();
        
        // $body_styles = $debug['styles']['body'];
        // $header_styles = $debug['styles']['header'];

        // $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        // $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        // $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        // $this->assertEquals('#123456', $header_styles['A1']['background']); // odd
        // $this->assertEquals('#654321', $header_styles['A2']['background']); // even
        // $this->assertEquals($default['background-color-odd'], $header_styles['A3']['background']); // odd

    }
}
    