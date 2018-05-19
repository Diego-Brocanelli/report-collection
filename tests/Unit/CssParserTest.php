<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Style;
use ReportCollection\Libs\CssParser;

class CssParserTest extends TestCase
{
    public function testBorderSetter()
    {
        $styles = [
            'border-top-style'    => 'thick',
            'border-right-style'  => 'dash-dot',
            'border-bottom-style' => 'dashed',
            'border-left-style'   => 'dotted',
        ];

        $parsed = CssParser::parse($styles);

        // Valores foram setados
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_THICK);
        $this->assertEquals($styles['border-right-style'], Style\Border::BORDER_DASHDOT);
        $this->assertEquals($styles['border-bottom-style'], Style\Border::BORDER_DASHED);
        $this->assertEquals($styles['border-left-style'], Style\Border::BORDER_DOTTED);
    }
    public function testBorderStyles()
    {
        $parsed = CssParser::parse(['border-top-style' => 'none']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_NONE);

        $parsed = CssParser::parse(['border-top-style' => 'dash-dot']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_DASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'dash-dot-dot']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_DASHDOTDOT);

        $parsed = CssParser::parse(['border-top-style' => 'dashed']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_DASHED);

        $parsed = CssParser::parse(['border-top-style' => 'dotted']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_DOTTED);

        $parsed = CssParser::parse(['border-top-style' => 'double']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_DOUBLE);

        $parsed = CssParser::parse(['border-top-style' => 'hair']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_HAIR);

        $parsed = CssParser::parse(['border-top-style' => 'medium']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_MEDIUM);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dash-dot']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_MEDIUMDASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dash-dot-dot']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_MEDIUMDASHDOTDOT);

        $parsed = CssParser::parse(['border-top-style' => 'medium-dashed']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_MEDIUMDASHED);

        $parsed = CssParser::parse(['border-top-style' => 'slant-dash-dot']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_SLANTDASHDOT);

        $parsed = CssParser::parse(['border-top-style' => 'thick']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_THICK);

        $parsed = CssParser::parse(['border-top-style' => 'thin']);
        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_THIN);
    }

/*    public function testAlignments()
    {
        $styles = [
            'border-top-style'   => 'thick',
            'border-right-style'   => 'dash-dot',
            'border-bottom-style'   => 'dashed',
            'border-left-style'   => 'dotted',

            'border-top-color'   => '#0000ff',
            'border-right-color'   => '#0000ff',
            'border-bottom-color'   => '#0000ff',
            'border-left-color'   => '#0000ff',

            'background-color'   => '#ff0000',
            'background-fill'    => 'dark-down',

            'color'              => '#fffff0',
            'font-face'          => 'Arial',
            'font-size'          => '11',
            'font-weight'        => 'bold',
            'font-style'         => 'italic',
            'line-height'        => '25',
            'text-align'         => 'center',
            'vertical-align'     => 'middle',
        ];

        $parsed = CssParser::parse($styles);

        $this->assertEquals($styles['border-top-style'], Style\Border::BORDER_THICK);
        $this->assertEquals($styles['border-right-style'], Style\Border::BORDER_THICK);
        $this->assertEquals($styles['border-bottom-style'], Style\Border::BORDER_THICK);
        $this->assertEquals($styles['border-left-style'], Style\Border::BORDER_THICK);

        $this->assertInstanceOf(Style\Color::class, $styles['background-top-color']);
        $this->assertInstanceOf(Style\Color::class, $styles['background-right-color']);
        $this->assertInstanceOf(Style\Color::class, $styles['background-bottom-color']);
        $this->assertInstanceOf(Style\Color::class, $styles['background-left-color']);

        $this->assertInstanceOf(Style\Color::class, $styles['background-color']);
        $this->assertEquals($styles['background-fill'], Style\Fill::FILL_PATTERN_DARKDOWN);
        $this->assertInstanceOf(Style\Color::class, $styles['color']);
        $this->assertEquals($styles['font-face'], 'Arial');
        $this->assertEquals($styles['font-size'], '11');
        $this->assertEquals($styles['font-weight'], 'bold');
        $this->assertEquals($styles['font-style'], 'italic');
        $this->assertEquals($styles['line-height'], '25');
        $this->assertEquals($styles['text-align'], Style\Alignment::HORIZONTAL_CENTER);
        $this->assertEquals($styles['vertical-align'], Style\Alignment::VERTICAL_CENTER);
    }*/
}