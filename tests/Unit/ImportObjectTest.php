<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Tests\Libs;

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

    public function testImportSimpleObjectTwo()
    {
        $provider = new Libs\ValidObject;

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
        $provider = new Libs\ObjectIterator;

        $handle = \ReportCollection::createFromObject($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportArrayIterator()
    {
        $provider = new \ArrayIterator([
            ["Company", "Contact", "Country"],
            ["Alfreds Futterkiste", "Maria Anders", "Germany"],
            ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
            ["Ernst Handel", "Roland Mendel", "Austria"],
            ["Island Trading", "Helen Bennett", "UK"],
            ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
            ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
        ]);

        $handle = \ReportCollection::createFromObject($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportObjectToArray()
    {
        // Illuminate\Support\Collection

        $provider = collect([
            ["Company", "Contact", "Country"],
            ["Alfreds Futterkiste", "Maria Anders", "Germany"],
            ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
            ["Ernst Handel", "Roland Mendel", "Austria"],
            ["Island Trading", "Helen Bennett", "UK"],
            ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
            ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
        ]);

        $handle = \ReportCollection::createFromObject($provider);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $provider = new Libs\InvalidObject;
        $handle = \ReportCollection::createFromObject($provider);
    }
}
