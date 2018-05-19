<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;

class WriteToXlsTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Country"],
        ["Alfreds Futterkiste", "Maria Anders", "Germany"],
        ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
        ["Ernst Handel", "Roland Mendel", "Austria"],
    );

    public function testWriteSimple()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Styler::createFromReader($reader);

        $styler->setStyles('1', [
            'border-top-style'   => 'thick',
            'border-top-color'   => '#0000ff',
            'background-color'   => '#ff0000',
            'background-fill'    => 'dark-down',
            'color'              => '#fffff0', // color deve ser atualizado
            'font-face'          => 'Arial',
            'font-size'          => '11',
            'font-weight'        => 'normal',
            'font-style'         => 'normal',
            'line-height'        => '25',
            'text-align'         => 'center',
            'vertical-align'     => 'middle',
        ]);

        $writer = Writer::createFromStyler($styler);

        $temp_file = tempnam(sys_get_temp_dir(), 'AAA') . ".xls";
        $writer->save($temp_file);
    }
}
