<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class ReaderToArrayTest extends TestCase
{
    public function testSave()
    {
        $provider = array(
            ["Company", "Contact", "Country"],
            ["Alfreds Futterkiste", "Maria Anders", "Germany"],
            ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
            ["Ernst Handel", "Roland Mendel", "Austria"],
            ["Island Trading", "Helen Bennett", "UK"],
            ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
            ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
        );

        $handle = Reader::createFromArray($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }
}
