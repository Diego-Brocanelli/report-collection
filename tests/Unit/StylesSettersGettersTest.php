<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StylesSettersGettersTest extends TestCase
{
    public function testDefaultStyles()
    {
        // Estilos padrões sempre existem
        $handle  = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));
        $default = $handle->getStyles();
        $this->assertTrue(count($default)>0);


        // A normalização adiciona torna os estilos iguais
        // se não forem setados no header ou body
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $default = $handle->getStyles();
        $total   = count($default);
        $header  = $handle->getStyles('header');
        $body    = $handle->getStyles('body');

        $this->assertCount($total, $header);
        $this->assertCount($total, $body);

    }

    public function testInvalidStylesClean()
    {
        // A normalização remove os estilos errados 
        // e adiciona os padrões se não forem setados no header ou body
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $handle->setHeaderStyles([
            'estilo-errado' => '#222222'
        ]);
        $handle->setBodyStyles([
            'estilo-errado' => '#222222'
        ]);

        $default = $handle->getStyles();
        $total   = count($default);

        $header  = $handle->getStyles('header');
        $body    = $handle->getStyles('body');

        $this->assertCount($total, $header);
        $this->assertEquals($header['background-color-odd'], '#555555');
        $this->assertEquals($header['background-color-even'], '#555555');
        $this->assertEquals($header['border-color-inside'], '#444444');
        $this->assertEquals($header['border-color-outside'], '#555555');
        $this->assertEquals($header['color'], '#ffffff');

        $this->assertCount($total, $body);
        $this->assertEquals($body['background-color-odd'], '#ffffff');
        $this->assertEquals($body['background-color-even'], '#f5f5f5');
        $this->assertEquals($body['border-color-inside'], '#eeeeee');
        $this->assertEquals($body['border-color-outside'], '#555555');
        $this->assertEquals($body['color'], '#555555');

    }

    public function testCustomHeaderStyle()
    {
        // A normalização adiciona o estilo setado e 
        // completa com os padrões padrões
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $default = $handle->getStyles('header');

        $handle->setHeaderStyles([
            'color' => '#222222'
        ]);

        $total   = count($default);

        $header  = $handle->getStyles('header');
        
        $this->assertCount($total, $header);
        $this->assertNotEquals($default, $header);

        // única diferença
        unset($default['color']);
        unset($header['color']);
        $this->assertEquals($default, $header);
    }

    public function testCustomBodyStyle()
    {
        // A normalização adiciona o estilo setado e 
        // completa com os padrões padrões
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $default = $handle->getStyles('body');

        $handle->setBodyStyles([
            'color' => '#222222'
        ]);

        $total   = count($default);

        $body  = $handle->getStyles('body');
        
        $this->assertCount($total, $body);
        $this->assertNotEquals($default, $body);

        // única diferença
        unset($default['color']);
        unset($body['color']);
        $this->assertEquals($default, $body);
    }
}
