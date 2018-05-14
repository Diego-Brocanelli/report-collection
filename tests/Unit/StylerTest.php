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

    public function testSetStyles()
    {
        // O método setStyles() usa o método resolveRange() para
        // transofrmar A3 em indices PHP (linha 0 e coluna 2).
        // Em seguida, passa os valores da linha e coluna para
        // o método protegido apllyStyles, testado anteriormente.

        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        // Setagem indiviual
        $styler->setStyles('A1', ['background-color' => '#f5f5f5']);
        $styler->setStyles('A1', ['color' => '#555555']);
        $styler->setStyles('A1', ['text-align' => 'left']);

        $data = $styler->getBuffer();
        // Setados
        $this->assertArrayHasKey('background-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('color', $data[0][0]['styles']);
        $this->assertArrayHasKey('text-align', $data[0][0]['styles']);
        $this->assertEquals('#f5f5f5', $data[0][0]['styles']['background-color']);
        $this->assertEquals('#555555', $data[0][0]['styles']['color']);
        $this->assertEquals('left', $data[0][0]['styles']['text-align']);
        // Não-setados
        $this->assertArrayNotHasKey('font-face', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('font-size', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('font-weight', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('font-style', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('line-height', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('vertical-align', $data[0][0]['styles']);

        // Setagem múltipla
        $styler->setStyles('A1', [
            //'background-color' => não muda
            'color'              => '#999999', // color deve ser atualizado
            'font-face'          => 'Arial',
            'font-size'          => '11',
            'font-weight'        => 'normal',
            'font-style'         => 'normal',
            'line-height'        => '25',
            //'text-align'       => não muda
            'vertical-align'     => 'middle',
        ]);

        $data = $styler->getBuffer();
        $this->assertArrayHasKey('background-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('color', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-face', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-size', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-weight', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-style', $data[0][0]['styles']);
        $this->assertArrayHasKey('line-height', $data[0][0]['styles']);
        $this->assertArrayHasKey('text-align', $data[0][0]['styles']);
        $this->assertArrayHasKey('vertical-align', $data[0][0]['styles']);

        $this->assertEquals('#f5f5f5', $data[0][0]['styles']['background-color']); // o mesmo valor
        $this->assertEquals('#999999', $data[0][0]['styles']['color']);
        $this->assertEquals('Arial', $data[0][0]['styles']['font-face']);
        $this->assertEquals('11', $data[0][0]['styles']['font-size']);
        $this->assertEquals('normal', $data[0][0]['styles']['font-weight']);
        $this->assertEquals('normal', $data[0][0]['styles']['font-style']);
        $this->assertEquals('25', $data[0][0]['styles']['line-height']);
        $this->assertEquals('left', $data[0][0]['styles']['text-align']); // o mesmo valor
        $this->assertEquals('middle', $data[0][0]['styles']['vertical-align']);

        // Setar como none, remove o estilo
        $styler->setStyles('A1', [
            'color'              => 'none',
            'font-style'         => 'none',
            'line-height'        => 'none',
        ]);

        $data = $styler->getBuffer();
        $this->assertArrayHasKey('background-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('font-style', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('line-height', $data[0][0]['styles']);

        $this->assertArrayHasKey('font-face', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-size', $data[0][0]['styles']);
        $this->assertArrayHasKey('font-weight', $data[0][0]['styles']);
        $this->assertArrayHasKey('text-align', $data[0][0]['styles']);
        $this->assertArrayHasKey('vertical-align', $data[0][0]['styles']);

        $data = $styler->getBuffer();
        $this->assertArrayNotHasKey('border-top-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-left-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-top-style', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-left-style', $data[0][0]['styles']);

        $styler->setStyles('A1', [
            'border-top-color'  => '#fff000',
            'border-left-color' => '#000000',
            'border-left-style' => 'dashed'
        ]);
        $styler->setStyles('A1', [
            'border-top-style'  => 'dotted',
        ]);
        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-top-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-left-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-top-style', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-left-style', $data[0][0]['styles']);
        $this->assertEquals('#fff000', $data[0][0]['styles']['border-top-color']);
        $this->assertEquals('#000000', $data[0][0]['styles']['border-left-color']);
        $this->assertEquals('dotted', $data[0][0]['styles']['border-top-style']);
        $this->assertEquals('dashed', $data[0][0]['styles']['border-left-style']);

        // Setar como none, remove o estilo
        $styler->setStyles('A1', [
            'border-left-color' => 'none',
            'border-left-style' => 'none',
        ]);

        $data = $styler->getBuffer();
        $this->assertArrayHasKey('border-top-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('border-top-style', $data[0][0]['styles']);
        $this->assertEquals('#fff000', $data[0][0]['styles']['border-top-color']);
        $this->assertEquals('dotted', $data[0][0]['styles']['border-top-style']);

        $this->assertArrayNotHasKey('border-left-color', $data[0][0]['styles']);
        $this->assertArrayNotHasKey('border-left-style', $data[0][0]['styles']);
    }

    public function testSetStylesException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $styler->setStyles('A1', ['super-mega-blaster' => '777']);
    }

    public function testSetStylesBoolean()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        /*----------A----------------+-------B---------+-------C--------+
      1 | Company                    | Contact         | Country        |
        +----------------------------+-----------------+----------------+
      2 | Alfreds Futterkiste        | Maria Anders    | Germany        |
        +----------------------------+-----------------+----------------+
      3 | Centro comercial Moctezuma | Francisco Chang | Mexico         |
        +----------------------------+-----------------+----------------+
      4 | Ernst Handel               | Roland Mendel   | Austria        |
        +----------------------------+-----------------+---------------*/

        // TRUE Rotina normal
        $this->assertTrue($styler->setStyles('C1', ['color' => '#777']));
        $this->assertTrue($styler->setStyles('A4', ['color' => '#777']));
        // TRUE Rotina de bordas
        $this->assertTrue($styler->setStyles('C1', ['border-top-color' => '#777']));
        $this->assertTrue($styler->setStyles('A4', ['border-top-color' => '#777']));

        // FALSE Rotina normal
        // Setagens para indices inexistentes retornam false
        $this->assertFalse($styler->setStyles('D1', ['color' => '#777']));
        $this->assertFalse($styler->setStyles('A5', ['color' => '#777']));
        // FALSE Rotina de bordas
        $this->assertFalse($styler->setStyles('D1', ['border-top-color' => '#777']));
        $this->assertFalse($styler->setStyles('A5', ['border-top-color' => '#777']));
    }

    public function testApplyStyles()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $styler->accessApplyStyles(0, 0, ['background-color' => '#f5f5f5']);
        $styler->accessApplyStyles(0, 0, ['color' => '#555555']);
        $styler->accessApplyStyles(0, 0, ['text-align' => 'left']);

        $data = $styler->getBuffer();
        $this->assertArrayHasKey('background-color', $data[0][0]['styles']);
        $this->assertArrayHasKey('color', $data[0][0]['styles']);
        $this->assertArrayHasKey('text-align', $data[0][0]['styles']);
    }

    public function testApplyStylesException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $styler->accessApplyStyles(0, 0, ['super-mega-blaster' => '777']);
    }

    public function testApplyStylesBoolean()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        /*-----------0---------------+--------1--------+--------2-------+
      0 | Company                    | Contact         | Country        |
        +----------------------------+-----------------+----------------+
      1 | Alfreds Futterkiste        | Maria Anders    | Germany        |
        +----------------------------+-----------------+----------------+
      2 | Centro comercial Moctezuma | Francisco Chang | Mexico         |
        +----------------------------+-----------------+----------------+
      3 | Ernst Handel               | Roland Mendel   | Austria        |
        +----------------------------+-----------------+---------------*/

        // TRUE Rotina normal
        $this->assertTrue($styler->accessApplyStyles(3, 0, ['color' => '#777']));
        $this->assertTrue($styler->accessApplyStyles(0, 2, ['color' => '#777']));
        // TRUE Rotina de bordas
        $this->assertTrue($styler->accessApplyStyles(3, 2, ['border-top-color' => '#777']));
        $this->assertTrue($styler->accessApplyStyles(0, 2, ['border-top-color' => '#777']));

        // FALSE Rotina normal
        // Setagens para indices inexistentes retornam false
        $this->assertFalse($styler->accessApplyStyles(4, 0, ['color' => '#777']));
        $this->assertFalse($styler->accessApplyStyles(0, 3, ['color' => '#777']));
        // FALSE Rotina de bordas
        $this->assertFalse($styler->accessApplyStyles(4, 0, ['border-top-color' => '#777']));
        $this->assertFalse($styler->accessApplyStyles(0, 3, ['border-top-color' => '#777']));

    }

    public function testApplyBorderStyleRow()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        /*-----------0---------------+--------1--------+--------2-------+
      0 | Company                    | Contact         | Country        |
        +----------------------------+-----------------+----------------+
      1 | Alfreds Futterkiste        | Maria Anders    | Germany        |
        +----------------------------+-----------------+----------------+
      2 | Centro comercial Moctezuma | Francisco Chang | Mexico         |
        +----------------------------+-----------------+----------------+
      3 | Ernst Handel               | Roland Mendel   | Austria        |
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
        $this->assertTrue(true);
    }



}
