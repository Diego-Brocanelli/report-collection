<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StylesTest extends TestCase
{
    public function testStylesGetterAndSetter()
    {
        // Estilos padrões empre existem
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));
        $default       = $handle->getStyles();
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
        $this->assertEquals($default, $header);
        $this->assertEquals($default, $body);


        // A normalização remove os estilos errados
        // e adiciona os padrões se não forem setados no header ou body
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $handle->setStyles('header', [
            'estilo-errado' => '#222222'
        ]);
        $handle->setStyles('body', [
            'estilo-errado' => '#222222'
        ]);

        $default = $handle->getStyles();
        $total   = count($default);
        $header  = $handle->getStyles('header');
        $body    = $handle->getStyles('body');

        $this->assertCount($total, $header);
        $this->assertCount($total, $body);
        $this->assertEquals($default, $header);
        $this->assertEquals($default, $body);

        // A normalização adiciona o estilo setado e 
        // completa com os padrões padrões
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $handle->setStyles('header', [
            'color' => '#222222'
        ]);

        $default = $handle->getStyles();
        $total   = count($default);

        $header  = $handle->getStyles('header');
        
        $this->assertCount($total, $header);
        $this->assertNotEquals($default, $header);

        // única diferença
        unset($default['color']);
        unset($header['color']);
        $this->assertEquals($default, $header);
    }
}
