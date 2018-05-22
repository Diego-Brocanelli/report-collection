<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;
use PhpOffice\PhpSpreadsheet\Style;

class WriteToXlsxTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Date"],
        ["Alfreds Futterkiste", "Maria Anders", "10/01/1980"],
        ["Centro comercial Moctezuma", "Francisco Chang", "20/02/1978"],
        ["Ernst Handel", "Roland Mendel", "26/06/1985"],
    );

    public function testWritedInfo()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Writer::createFromReader($reader);
        $writer->setInfoCreator('aaa');
        $writer->setInfoLastModifiedBy('bbb');
        $writer->setInfoTitle('ccc');
        $writer->setInfoSubject('ddd');
        $writer->setInfoDescription('eee');
        $writer->setInfoKeywords('fff');
        $writer->setInfoCategory('ggg');

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsInfoTest') . ".xlsx";
        $writer->save($temp_file);
        $this->assertFileExists($temp_file);

        // O arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);

        $props = $reader->getBuffer()->getProperties();
        $this->assertEquals('aaa', $props->getCreator());
        $this->assertEquals('bbb', $props->getLastModifiedBy());
        $this->assertEquals('ccc', $props->getTitle());
        $this->assertEquals('ddd', $props->getSubject());
        $this->assertEquals('eee', $props->getDescription());
        $this->assertEquals('fff', $props->getKeywords());
        $this->assertEquals('ggg', $props->getCategory());
    }

    public function testWritedStyles()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Styler::createFromReader($reader);
        $styler->setStyles('A1', [
            'background-color'    => '#999999',
            'background-fill'     => 'solid',
            'border-top-color'    => '#ff0000',
            'border-right-color'  => '#00ff00',
            'border-bottom-color' => '#0000ff',
            'border-left-color'   => '#f0000f',
            'border-top-style'    => 'thick',
            'border-right-style'  => 'thick',
            'border-bottom-style' => 'thick',
            'border-left-style'   => 'thick',
            'color'          => '#eeeeee',
            'font-face'      => 'Arial',
            'font-size'      => '11',
            'font-weight'    => 'bold',
            'font-style'     => 'italic',
            'line-height'    => '25',
            'text-align'     => 'center',
            'vertical-align' => 'middle',
        ]);
        $writer = Writer::createFromStyler($styler);

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsTest') . ".xlsx";
        $writer->save($temp_file);
        $this->assertFileExists($temp_file);

        // O arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);

        $cell = $reader->getBuffer()->getActiveSheet()->getStyle('A1');

        // background-color
        $this->assertEquals('999999', $cell->getFill()->getStartColor()->getRGB());
        $this->assertEquals('FF999999', $cell->getFill()->getStartColor()->getARGB());

        // background-color
        $this->assertEquals(Style\Fill::FILL_SOLID, $cell->getFill()->getFillType());

        // border-xx-color
        $this->assertEquals('FF0000', $cell->getBorders()->getTop()->getColor()->getRGB());
        $this->assertEquals('FFFF0000', $cell->getBorders()->getTop()->getColor()->getARGB());
        $this->assertEquals('00FF00', $cell->getBorders()->getRight()->getColor()->getRGB());
        $this->assertEquals('FF00FF00', $cell->getBorders()->getRight()->getColor()->getARGB());
        $this->assertEquals('0000FF', $cell->getBorders()->getBottom()->getColor()->getRGB());
        $this->assertEquals('FF0000FF', $cell->getBorders()->getBottom()->getColor()->getARGB());
        $this->assertEquals('F0000F', $cell->getBorders()->getLeft()->getColor()->getRGB());
        $this->assertEquals('FFF0000F', $cell->getBorders()->getLeft()->getColor()->getARGB());

        // border-xx-style
        $this->assertEquals(Style\Border::BORDER_THICK, $cell->getBorders()->getTop()->getBorderStyle());
        $this->assertEquals(Style\Border::BORDER_THICK, $cell->getBorders()->getRight()->getBorderStyle());
        $this->assertEquals(Style\Border::BORDER_THICK, $cell->getBorders()->getBottom()->getBorderStyle());
        $this->assertEquals(Style\Border::BORDER_THICK, $cell->getBorders()->getLeft()->getBorderStyle());

        // color
        $this->assertEquals('EEEEEE', $cell->getFont()->getColor()->getRGB());
        $this->assertEquals('FFEEEEEE', $cell->getFont()->getColor()->getARGB());

        // font-weight
        $this->assertTrue($cell->getFont()->getBold());

        // font-style
        $this->assertTrue($cell->getFont()->getItalic());

        // font-face
        $this->assertEquals('Arial', $cell->getFont()->getName());

        // font-size
        $this->assertEquals(11, $cell->getFont()->getSize());

        // text-align
        $this->assertEquals(Style\Alignment::HORIZONTAL_CENTER, $cell->getAlignment()->getHorizontal());

        // vertical-align
        $this->assertEquals(Style\Alignment::VERTICAL_CENTER, $cell->getAlignment()->getVertical());

        // line-height
        $this->assertEquals(25, $reader->getBuffer()->getActiveSheet()->getRowDimension(1)->getRowHeight());
    }
}
