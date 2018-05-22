<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;
use PhpOffice\PhpSpreadsheet\Style;

class WriteToCsvTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Date"],
        ["Alfreds Futterkiste", "Maria Anders", "10/01/1980"],
        ["Centro comercial Moctezuma", "Francisco Chang", "20/02/1978"],
        ["Ernst Handel", "Roland Mendel", "26/06/1985"],
    );

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

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterToCsvTest') . ".csv";
        $writer->save($temp_file);
        $this->assertFileExists($temp_file);

        // O arquivo gravado está legível
        $handle = fopen($temp_file, "r");
        $this->assertTrue($handle !== FALSE);

        $list = [];
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $list[$row] = [];
            foreach ($data as $col => $value) {
                $list[$row][] = $value;
            }

            $row++;
        }
        fclose($handle);

        $this->assertEquals($this->provider, $list);
    }
}
