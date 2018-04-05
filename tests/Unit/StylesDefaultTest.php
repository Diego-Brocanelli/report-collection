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
    public function testStyles()
    {
        $file = __DIR__ . '/../Files/table.xls';

        // Padroes 
        // ------------------------------------------------------------------

        $handle  = \ReportCollection::createFromFile($file);
        $default = $handle->getStyles();
        $header  = $handle->getStyles('header');
        $body    = $handle->getStyles('body');

        $handle->save($this->filename('styles-default'));

        $debug = $handle->getDebugInfo();
        $default_styles = $debug['styles']['default'];
        
        $body_styles = $debug['styles']['body'];

        $this->assertEquals($default['border-style-inside'], $header['border-style-inside']);
        $this->assertEquals($default['border-style-outside'], $header['border-style-outside']);
        $this->assertEquals($default['border-style-inside'], $body['border-style-inside']);
        $this->assertEquals($default['border-style-outside'], $body['border-style-outside']);

        $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        // Setando padrões 
        // ------------------------------------------------------------------

        $handle = \ReportCollection::createFromFile($file);
        $handle->setBodyStyles($default_styles);
        $handle->save($this->filename('styles-default-setted'));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];

        $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        // Estilos padrões com Cabeçalho
        // ------------------------------------------------------------------

        $handle = \ReportCollection::createFromFile($file);

        $handle->addHeaderRow('<b>Report Collection</b>');
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>');
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');
        $handle->save($this->filename('styles-default-header'));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];
        $header_styles = $debug['styles']['header'];

        $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        $this->assertEquals($header['background-color-odd'], $header_styles['A1']['background']);
        $this->assertEquals($header['background-color-even'], $header_styles['A2']['background']);

        // Estilos personalizados com Cabeçalho
        // ------------------------------------------------------------------

        $handle = \ReportCollection::createFromFile($file);

        $handle->addHeaderRow('<b>Report Collection</b>', ['background-color' => '#123456']);
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>', ['background-color' => '#654321']);
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');

        $handle->save($this->filename('styles-custom-header'));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];
        $header_styles = $debug['styles']['header'];

        $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        $this->assertEquals('#123456', $header_styles['A1']['background']);
        $this->assertEquals('#654321', $header_styles['A2']['background']);
        $this->assertEquals($header['background-color-odd'], $header_styles['A3']['background']);


        // Estilos personalizados intermitentes com Cabeçalho
        // ------------------------------------------------------------------

        $handle = \ReportCollection::createFromFile($file);

        $handle->addHeaderRow('<b>Report Collection</b>', ['background-color-odd' => '#123456']);
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>', ['background-color-even' => '#654321']);
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');

        $handle->setHeaderStyles($default_styles);
        $handle->setBodyStyles($default_styles);
        $handle->save($this->filename('styles-custom-header-split'));

        $debug = $handle->getDebugInfo();
        
        $body_styles = $debug['styles']['body'];
        $header_styles = $debug['styles']['header'];

        $this->assertEquals($default['background-color-odd'], $default_styles['background']);
        $this->assertEquals($default['background-color-odd'], $body_styles['A1']['background']);
        $this->assertEquals($default['background-color-even'], $body_styles['A2']['background']);

        $this->assertEquals('#123456', $header_styles['A1']['background']); // odd
        $this->assertEquals('#654321', $header_styles['A2']['background']); // even
        $this->assertEquals($default['background-color-odd'], $header_styles['A3']['background']); // odd

    }
}
