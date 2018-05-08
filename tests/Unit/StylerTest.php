<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Tests\Libs;

class StylerTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Country"],
        ["Alfreds Futterkiste", "Maria Anders", "Germany"],
        ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
        ["Ernst Handel", "Roland Mendel", "Austria"],
    );

    public function testBuffer()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Styler::createFromReader($reader);

        $this->assertEquals($styler->getBuffer(), [
            0 => array(
                0 => [ "value" => "Company", "styles" => [] ],
                1 => [ "value" => "Contact", "styles" => [] ],
                2 => [ "value" => "Country", "styles" => [] ]
            ),
            1 => array(
              0 => [ "value" => "Alfreds Futterkiste", "styles" => [] ],
              1 => [ "value" => "Maria Anders", "styles" => [] ],
              2 => [ "value" => "Germany", "styles" => [] ]
            ),
            2 => array(
              0 => [ "value" => "Centro comercial Moctezuma", "styles" => [] ],
              1 => [ "value" => "Francisco Chang", "styles" => [] ],
              2 => [ "value" => "Mexico", "styles" => [] ]
            ),
            3 => array(
              0 => [ "value" => "Ernst Handel", "styles" => [] ],
              1 => [ "value" => "Roland Mendel", "styles" => [] ],
              2 => [ "value" => "Austria", "styles" => [] ]
            )
        ]);
    }

    public function testColumnNumber()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        // O cálculo da numeração resultante é baseana no modo excel, começando com 1
        $this->assertEquals($styler->accessGetColumnNumber('AZ'), 52);
        $this->assertEquals($styler->accessGetColumnNumber('C'), 3);
        $this->assertEquals($styler->accessGetColumnNumber('ZZ'), 702);
    }

    public function testResolveRange()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        // O cálculo da numeração resultante é baseana no índice do excel, começando com 1
        // Subtrai-se aqui para testar o resultado da resolução, que é baseana no índice do PHP, começando com 0
        $az = $styler->accessGetColumnNumber('AZ') - 1;
        $c = $styler->accessGetColumnNumber('C') - 1;
        $zz = $styler->accessGetColumnNumber('ZZ') - 1;

        $this->assertEquals($styler->accessResolveRange('AZ22'), ['row' => 21, 'col' => $az]);
        $this->assertEquals($styler->accessResolveRange('C5'), ['row' => 4, 'col' => $c]);
        $this->assertEquals($styler->accessResolveRange('ZZ333'), ['row' => 332, 'col' => $zz]);

        $this->assertEquals($styler->accessResolveRange('22'), ['row' => 21, 'col' => null]);
        $this->assertEquals($styler->accessResolveRange(45), ['row' => 44, 'col' => null]);
    }

    public function testResolveRangeException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $this->assertEquals($styler->accessResolveRange('AZ'));
    }

    public function testApplyBorderStyleRow()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        /*---------------------------+-----------------+----------------+
        | Company                    | Contact         | Country        |
        +----------------------------+-----------------+----------------+
        | Alfreds Futterkiste        | Maria Anders    | Germany        |
        +----------------------------+-----------------+----------------+
        | Centro comercial Moctezuma | Francisco Chang | Mexico         |
        +----------------------------+-----------------+----------------+
        | Ernst Handel               | Roland Mendel   | Austria        |
        +----------------------------+-----------------+---------------*/

        // NOTA 1:
        // Os estilos de borda são aplicados apenas no topo e na esquerda
        // Isso diminui a carga na estilização da planilha e corrige possiveis
        // bugs no objeto Spreadsheet

        // NOTA 2:
        // A numeração de $row e $col é baseana no índice do PHP, começando com 0

        // TOP SET Linha 1 ------------------------------------------------------
        // O border-top é sempre setado explicitamente
        $this->assertTrue($styler->accessApplyBorderStyle(0, 0, 'border-top-color', '#000000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 1, 'border-top-color', '#110000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 2, 'border-top-color', '#220000'));
        // Linha e coluna inexistentes
        $this->assertFalse($styler->accessApplyBorderStyle(9, 0, 'border-top-color', '#330000'));
        $this->assertFalse($styler->accessApplyBorderStyle(0, 3, 'border-top-color', '#330000'));

        // TOP RESULTADO Linha 1 ------------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-top-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[0][1]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[0][2]['styles']);
        $this->assertEquals($data[0][0]['styles']['border-top-color'], '#000000');
        $this->assertEquals($data[0][1]['styles']['border-top-color'], '#110000');
        $this->assertEquals($data[0][2]['styles']['border-top-color'], '#220000');

        // BOTTOM SET Linha 1 ------------------------------------------------------
        // Excetuando a última linha, border-bottom é setado como border-top da próxima linha
        $this->assertTrue($styler->accessApplyBorderStyle(0, 0, 'border-bottom-color', '#550000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 1, 'border-bottom-color', '#660000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 2, 'border-bottom-color', '#770000'));

        // BOTTOM RESULTADO Linha 1 ------------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayNotHasKey('border-bottom-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-bottom-color', $data[0][1]['styles']);
        $this->assertArrayNotHasKey('border-bottom-color', $data[0][2]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[0][1]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[0][2]['styles']);
        $this->assertEquals($data[0][0]['styles']['border-top-color'], '#000000');
        $this->assertEquals($data[0][1]['styles']['border-top-color'], '#110000');
        $this->assertEquals($data[0][2]['styles']['border-top-color'], '#220000');

        // BOTTOM RESULTADO Linha 2 ------------------------------------------------------
        $this->assertArrayNotHasKey('border-bottom-color', $data[1][0]['styles']);
        $this->assertArrayNotHasKey('border-bottom-color', $data[1][1]['styles']);
        $this->assertArrayNotHasKey('border-bottom-color', $data[1][2]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[1][0]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[1][1]['styles']);
        $this->assertArrayHasKey('border-top-color', $data[1][2]['styles']);
        $this->assertEquals($data[1][0]['styles']['border-top-color'], '#550000'); // border-bottom da linha anterior
        $this->assertEquals($data[1][1]['styles']['border-top-color'], '#660000'); // border-bottom da linha anterior
        $this->assertEquals($data[1][2]['styles']['border-top-color'], '#770000'); // border-bottom da linha anterior

        // BOTTOM SET ÚLTIMA LINHA ------------------------------------------------------
        // Na ultima linha, border-bottom é setado explicitamente
        $this->assertTrue($styler->accessApplyBorderStyle(3, 0, 'border-bottom-color', '#880000'));
        $this->assertTrue($styler->accessApplyBorderStyle(3, 1, 'border-bottom-color', '#990000'));
        $this->assertTrue($styler->accessApplyBorderStyle(3, 2, 'border-bottom-color', '#ff0000'));

        // BOTTOM RESULTADO ÚLTIMA LINHA --------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-bottom-color', $data[3][0]['styles']);
        $this->assertArrayHasKey('border-bottom-color', $data[3][1]['styles']);
        $this->assertArrayHasKey('border-bottom-color', $data[3][2]['styles']);
        $this->assertEquals($data[3][0]['styles']['border-bottom-color'], '#880000');
        $this->assertEquals($data[3][1]['styles']['border-bottom-color'], '#990000');
        $this->assertEquals($data[3][2]['styles']['border-bottom-color'], '#ff0000');
    }

    public function testResolveBorderStyleCol()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        /*---------------------------+-----------------+----------------+
        | Company                    | Contact         | Country        |
        +----------------------------+-----------------+----------------+
        | Alfreds Futterkiste        | Maria Anders    | Germany        |
        +----------------------------+-----------------+----------------+
        | Centro comercial Moctezuma | Francisco Chang | Mexico         |
        +----------------------------+-----------------+----------------+
        | Ernst Handel               | Roland Mendel   | Austria        |
        +----------------------------+-----------------+---------------*/

        // NOTA 1:
        // Os estilos de borda são aplicados apenas no topo e na esquerda
        // Isso diminui a carga na estilização da planilha e corrige possiveis
        // bugs no objeto Spreadsheet

        // NOTA 2:
        // A numeração de $row e $col é baseana no índice do PHP, começando com 0

        // LEFT SET ------------------------------------------------------
        // O border-left é sempre setado explicitamente
        $this->assertTrue($styler->accessApplyBorderStyle(0, 0, 'border-left-color', '#000000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 1, 'border-left-color', '#110000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 2, 'border-left-color', '#220000'));
        // Linha e coluna inexistentes
        $this->assertFalse($styler->accessApplyBorderStyle(9, 0, 'border-left-color', '#330000'));
        $this->assertFalse($styler->accessApplyBorderStyle(0, 3, 'border-left-color', '#330000'));

        // LEFT RESULTADO Coluna 1, 2 e 3 ------------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-left-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][1]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][2]['styles']);
        $this->assertEquals($data[0][0]['styles']['border-left-color'], '#000000');
        $this->assertEquals($data[0][1]['styles']['border-left-color'], '#110000');
        $this->assertEquals($data[0][2]['styles']['border-left-color'], '#220000');

        // RIGHT SET ------------------------------------------------------
        // Excetuando a última coluna, border-right é setado como border-left da próxima linha
        $this->assertTrue($styler->accessApplyBorderStyle(0, 0, 'border-right-color', '#550000'));
        $this->assertTrue($styler->accessApplyBorderStyle(0, 1, 'border-right-color', '#660000'));

        // RIGHT RESULTADO Coluna 1 e 2 ------------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayNotHasKey('border-right-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-right-color', $data[0][1]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][1]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][2]['styles']);
        $this->assertEquals($data[0][0]['styles']['border-left-color'], '#000000');
        $this->assertEquals($data[0][1]['styles']['border-left-color'], '#550000');

        // RIGHT SET ÚLTIMA COLUNA ------------------------------------------------------
        // Na ultima linha, border-right é setado explicitamente
        $this->assertTrue($styler->accessApplyBorderStyle(0, 2, 'border-right-color', '#770000'));

        // RIGHT RESULTADO ÚLTIMA COLUNA ------------------------------------------------------
        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-left-color', $data[0][2]['styles']);
        $this->assertArrayHasKey('border-right-color', $data[0][2]['styles']);
        $this->assertEquals($data[0][2]['styles']['border-right-color'], '#770000');
    }

    public function testApplyBorderStyleFromStylesSetter()
    {
        $this->asserTrue(true);
    }

    public function testApplyStyles()
    {
        $this->asserTrue(true);
    }

}
