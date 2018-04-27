<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class CreateFromArrayTest extends TestCase
{
    public function testCreateFromArray()
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
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(6, $array);
        $this->assertFalse(isset($array[7]));

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
            $this->assertArrayHasKey(0, $array[$x]);
            $this->assertArrayHasKey(1, $array[$x]);
            $this->assertArrayHasKey(2, $array[$x]);
        }
    }
}
