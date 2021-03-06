<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;
use PhpOffice\PhpSpreadsheet\Style;

class WriteToXlsTest extends TestCase
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

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsInfoTest') . ".xls";
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

    public function testWritedColumnWidths()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Writer::createFromReader($reader);
        $writer->setColumnWidth('A', 50);
        $writer->setColumnWidth('B', 40);
        $writer->setColumnWidth('C', 30);

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsColumnsTest_') . ".xls";
        $writer->save($temp_file);
        $this->assertFileExists($temp_file);

        // O arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);

        $sheet = $reader->getBuffer()->getActiveSheet();
        $this->assertEquals(50, $sheet->getColumnDimension('A')->getWidth());
        $this->assertEquals(40, $sheet->getColumnDimension('B')->getWidth());
        $this->assertEquals(30, $sheet->getColumnDimension('C')->getWidth());
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

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsStylesTest_') . ".xls";
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

    public function testNumericScientific()
    {
        $provider = array(
            ["Company", "Contact", "Date"],
            [ 359571084857470,  359571084848651,  351837098378052]
        );

        $reader = Reader::createFromArray($provider);
        $writer = Writer::createFromReader($reader);

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToXlsScientificTest') . ".xls";
        $writer->save($temp_file);
        $this->assertFileExists($temp_file);

        // O arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);

        $a2 = $reader->getBuffer()->getActiveSheet()->getCell('A2')->getValue();
        $b2 = $reader->getBuffer()->getActiveSheet()->getCell('B2')->getValue();
        $c2 = $reader->getBuffer()->getActiveSheet()->getCell('C2')->getCalculatedValue();

        // Internamente a biblioteca transforma números longos para strings!
        // Isso é proposital para evitar a formatação automática que gera números científicos
        // Por exemplo, '359571084857470' se tornaria '3.5957108485747E+14' na autoformatação
        $this->assertEquals('359571084857470', $a2);
        $this->assertEquals('359571084848651', $b2);
        $this->assertEquals('351837098378052', $c2);
    }
}
