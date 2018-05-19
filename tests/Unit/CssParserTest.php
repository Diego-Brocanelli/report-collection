<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Style;
use ReportCollection\Libs\CssParser;
use ReportCollection\Tests\Libs;

class CssParserTest extends TestCase
{
    public function testParseHex()
    {
        $parsed = Libs\CssParserAccessor::accessParseHex('#CCC');
        $this->assertEquals('FFCCCCCC', $parsed);

        $parsed = Libs\CssParserAccessor::accessParseHex('#CCCCCC');
        $this->assertEquals('FFCCCCCC', $parsed);

        $parsed = Libs\CssParserAccessor::accessParseHex('#CCCCCCCC');
        $this->assertEquals('CCCCCCCC', $parsed);

        $parsed = Libs\CssParserAccessor::accessParseHex('#CCCCCCCC');
        $this->assertEquals('CCCCCCCC', $parsed);

        // Cores inválidas são tratadas como preto
        $parsed = Libs\CssParserAccessor::accessParseHex('Hex totalmente inválico');
        $this->assertEquals('FF000000', $parsed);
    }

    public function testBorderStylesSetter()
    {
        $styles = [
            'border-top-style'    => 'thick',
            'border-right-style'  => 'dash-dot',
            'border-bottom-style' => 'dashed',
            'border-left-style'   => 'dotted',
        ];

        $parsed = CssParser::parse($styles);

        // Valores foram setados
        $this->assertArrayHasKey('border-top-style', $parsed);
        $this->assertArrayHasKey('border-right-style', $parsed);
        $this->assertArrayHasKey('border-bottom-style', $parsed);
        $this->assertArrayHasKey('border-left-style', $parsed);
    }
    public function testBorderStyles()
    {
        $parsed = CssParser::parse(['border-top-style' => 'none']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_NONE);

        $parsed = CssParser::parse(['border-top-style' => 'dash-dot']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_DASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'dash-dot-dot']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_DASHDOTDOT);

        $parsed = CssParser::parse(['border-top-style' => 'dashed']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_DASHED);

        $parsed = CssParser::parse(['border-top-style' => 'dotted']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_DOTTED);

        $parsed = CssParser::parse(['border-top-style' => 'double']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_DOUBLE);

        $parsed = CssParser::parse(['border-top-style' => 'hair']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_HAIR);

        $parsed = CssParser::parse(['border-top-style' => 'medium']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_MEDIUM);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dash-dot']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_MEDIUMDASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dash-dot-dot']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_MEDIUMDASHDOTDOT);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dashed']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_MEDIUMDASHED);

        $parsed = CssParser::parse(['border-top-style' => 'slant-dash-dot']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_SLANTDASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'thick']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_THICK);

        $parsed = CssParser::parse(['border-top-style' => 'thin']);
        $this->assertEquals($parsed['border-top-style'], Style\Border::BORDER_THIN);
    }

    public function testBorderColorSetter()
    {
        $parsed = CssParser::parse([
            'border-top-color' => '#ffffff',
            'border-right-color' => '#ffff00',
            'border-bottom-color' => '#ff00ff',
            'border-left-color' => '#00ffff'
        ]);

        $this->assertArrayHasKey('border-top-color', $parsed);
        $this->assertArrayHasKey('border-right-color', $parsed);
        $this->assertArrayHasKey('border-bottom-color', $parsed);
        $this->assertArrayHasKey('border-left-color', $parsed);
    }

    public function testBorderColor()
    {
        $parsed = CssParser::parse(['border-top-color' => '#ff00ff']);
        $this->assertInstanceOf(Style\Color::class, $parsed['border-top-color']);
        $this->assertEquals('FF00FF', $parsed['border-top-color']->getRGB());
        $this->assertEquals('FFFF00FF', $parsed['border-top-color']->getARGB());

        // Cor inválida é aplicada como preto
        $parsed = CssParser::parse(['border-top-color' => 'none']);
        $this->assertInstanceOf(Style\Color::class, $parsed['border-top-color']);
        $this->assertEquals('000000', $parsed['border-top-color']->getRGB());
        $this->assertEquals('FF000000', $parsed['border-top-color']->getARGB());
    }

    public function testColor()
    {
        $parsed = CssParser::parse(['color' => '#ffffff']);
        $this->assertArrayHasKey('color', $parsed);

        $parsed = CssParser::parse(['color' => '#ff00ff']);
        $this->assertInstanceOf(Style\Color::class, $parsed['color']);
        $this->assertEquals('FF00FF', $parsed['color']->getRGB());
        $this->assertEquals('FFFF00FF', $parsed['color']->getARGB());
    }

    public function testBackgroundColor()
    {
        $parsed = CssParser::parse(['background-color' => '#ffffff']);
        $this->assertArrayHasKey('background-color', $parsed);

        $parsed = CssParser::parse(['background-color' => '#ff00ff']);
        $this->assertInstanceOf(Style\Color::class, $parsed['background-color']);
        $this->assertEquals('FF00FF', $parsed['background-color']->getRGB());
        $this->assertEquals('FFFF00FF', $parsed['background-color']->getARGB());
    }

    public function testBackgroundFill()
    {
        $parsed = CssParser::parse(['background-fill' => 'solid']);
        $this->assertArrayHasKey('background-fill', $parsed);

        $parsed = CssParser::parse(['background-fill' => 'none']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_NONE);

        $parsed = CssParser::parse(['background-fill' => 'solid']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_SOLID);

        $parsed = CssParser::parse(['background-fill' => 'gradient-linear']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_GRADIENT_LINEAR);

        $parsed = CssParser::parse(['background-fill' => 'gradient-path']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_GRADIENT_PATH);

        $parsed = CssParser::parse(['background-fill' => 'dark-down']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKDOWN);

        $parsed = CssParser::parse(['background-fill' => 'dark-gray']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKGRAY);

        $parsed = CssParser::parse(['background-fill' => 'dark-grid']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKGRID);

        $parsed = CssParser::parse(['background-fill' => 'dark-horizontal']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKHORIZONTAL);

        $parsed = CssParser::parse(['background-fill' => 'dark-trellis']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKTRELLIS);

        $parsed = CssParser::parse(['background-fill' => 'dark-up']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKUP);

        $parsed = CssParser::parse(['background-fill' => 'dark-vertical']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_DARKVERTICAL);

        $parsed = CssParser::parse(['background-fill' => 'gray-0625']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_GRAY0625);

        $parsed = CssParser::parse(['background-fill' => 'gray-125']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_GRAY125);

        $parsed = CssParser::parse(['background-fill' => 'light-down']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTDOWN);

        $parsed = CssParser::parse(['background-fill' => 'light-gray']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTGRAY);

        $parsed = CssParser::parse(['background-fill' => 'light-grid']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTGRID);

        $parsed = CssParser::parse(['background-fill' => 'light-horizontal']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTHORIZONTAL);

        $parsed = CssParser::parse(['background-fill' => 'light-trellis']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTTRELLIS);

        $parsed = CssParser::parse(['background-fill' => 'light-up']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTUP);

        $parsed = CssParser::parse(['background-fill' => 'light-vertical']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_LIGHTVERTICAL);

        $parsed = CssParser::parse(['background-fill' => 'medium-gray']);
        $this->assertEquals($parsed['background-fill'], Style\Fill::FILL_PATTERN_MEDIUMGRAY);

        // TODO:
        // testar quando o background for setado sem fill
    }

    public function testTextAlign()
    {
        $parsed = CssParser::parse(['text-align' => 'center']);
        $this->assertEquals(Style\Alignment::HORIZONTAL_CENTER, $parsed['text-align']);

        $parsed = CssParser::parse(['text-align' => 'left']);
        $this->assertEquals(Style\Alignment::HORIZONTAL_LEFT, $parsed['text-align']);

        $parsed = CssParser::parse(['text-align' => 'right']);
        $this->assertEquals(Style\Alignment::HORIZONTAL_RIGHT, $parsed['text-align']);

        $parsed = CssParser::parse(['text-align' => 'justify']);
        $this->assertEquals(Style\Alignment::HORIZONTAL_JUSTIFY, $parsed['text-align']);
    }

    public function testVerticalAlign()
    {
        $parsed = CssParser::parse(['vertical-align' => 'top']);
        $this->assertEquals(Style\Alignment::VERTICAL_TOP, $parsed['vertical-align']);

        $parsed = CssParser::parse(['vertical-align' => 'bottom']);
        $this->assertEquals(Style\Alignment::VERTICAL_BOTTOM, $parsed['vertical-align']);

        $parsed = CssParser::parse(['vertical-align' => 'middle']);
        $this->assertEquals(Style\Alignment::VERTICAL_CENTER, $parsed['vertical-align']);
    }

    public function testLineHeight()
    {
        $parsed = CssParser::parse(['line-height' => 25]);
        $this->assertEquals(25, $parsed['line-height']);

        $parsed = CssParser::parse(['line-height' => '30']);
        $this->assertEquals(30, $parsed['line-height']);

        $parsed = CssParser::parse(['line-height' => '35px']);
        $this->assertEquals(35, $parsed['line-height']);

        $parsed = CssParser::parse(['line-height' => '40pt']);
        $this->assertEquals(40, $parsed['line-height']);
    }

    public function testFontSize()
    {
        $parsed = CssParser::parse(['font-size' => 25]);
        $this->assertEquals(25, $parsed['font-size']);

        $parsed = CssParser::parse(['font-size' => '30']);
        $this->assertEquals(30, $parsed['font-size']);

        $parsed = CssParser::parse(['font-size' => '35px']);
        $this->assertEquals(35, $parsed['font-size']);

        $parsed = CssParser::parse(['font-size' => '40pt']);
        $this->assertEquals(40, $parsed['font-size']);
    }

    public function testFontFace()
    {
        $parsed = CssParser::parse(['font-face' => 'Arial']);
        $this->assertEquals('Arial', $parsed['font-face']);
    }

    public function testFontWeight()
    {
        $parsed = CssParser::parse(['font-weight' => 'normal']);
        $this->assertEquals(false, $parsed['font-weight']);

        $parsed = CssParser::parse(['font-weight' => 'bold']);
        $this->assertEquals(true, $parsed['font-weight']);
    }

    public function testFontStyle()
    {
        $parsed = CssParser::parse(['font-style' => 'normal']);
        $this->assertEquals(false, $parsed['font-style']);

        $parsed = CssParser::parse(['font-style' => 'italic']);
        $this->assertEquals(true, $parsed['font-style']);
    }
}
