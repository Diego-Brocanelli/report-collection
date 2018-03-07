<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Tests\Unit\ImportObjectTestIterator;

class ImportObjectTest extends TestCase
{
    public function testImportSimpleObject()
    {
        $provider = (object) array(
            (object) ["Company", "Contact", "Country"],
            (object) ["Alfreds Futterkiste", "Maria Anders", "Germany"],
            (object) ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
            (object) ["Ernst Handel", "Roland Mendel", "Austria"],
            (object) ["Island Trading", "Helen Bennett", "UK"],
            (object) ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
            (object) ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
        );

        $handle = \ReportCollection::createFromObject($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportIteratorObject()
    {
        $provider = new ImportObjectTestIterator;

        $handle = \ReportCollection::createFromObject($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }
}
