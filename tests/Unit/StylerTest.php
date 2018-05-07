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

    public function testResolverRange()
    {
        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $az = $styler->accessGetColumnNumber('AZ');
        $c = $styler->accessGetColumnNumber('C');
        $zz = $styler->accessGetColumnNumber('ZZ');

        $this->assertEquals($styler->accessResolveRange('AZ22'), ['row' => 22, 'col' => $az]);
        $this->assertEquals($styler->accessResolveRange('C5'), ['row' => 5, 'col' => $c]);
        $this->assertEquals($styler->accessResolveRange('ZZ333'), ['row' => 333, 'col' => $zz]);

        $this->assertEquals($styler->accessResolveRange('22'), ['row' => 22, 'col' => null]);
        $this->assertEquals($styler->accessResolveRange(45), ['row' => 45, 'col' => null]);

    }

    public function testResolverRangeException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $styler = Libs\StylerAccessor::createFromReader($reader);

        $this->assertEquals($styler->accessResolveRange('AZ'));

    }
}
