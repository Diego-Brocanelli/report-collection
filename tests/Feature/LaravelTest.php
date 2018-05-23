<?php

namespace ReportCollection\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection;
use ReportCollection\Tests\Libs;

class LaravelTest extends TestCase
{
    public function testFromArray()
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

        $handle = ReportCollection::createFromArray($provider);

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

    public function testFromCsv()
    {
        $file = __DIR__ . '/../Files/table.csv';

        $handle = ReportCollection::createFromCsv($file);

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

    public function testFromGnumeric()
    {
        $file = __DIR__ . '/../Files/table.gnumeric';

        $handle = ReportCollection::createFromGnumeric($file);

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

    public function testFromHtml()
    {
        $file = __DIR__ . '/../Files/table.html';

        $handle = ReportCollection::createFromHtml($file);

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

    public function testFromHtmlString()
    {
        $contents = file_get_contents(__DIR__ . '/../Files/table.html');

        $handle = ReportCollection::createFromHtmlString($contents);

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

    public function testFromSimpleObject()
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

        $handle = ReportCollection::createFromObject($provider);

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

    public function testFromSimpleObjectTwo()
    {
        $provider = new Libs\ValidObject;

        $handle = ReportCollection::createFromObject($provider);

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

    public function testFromIteratorObject()
    {
        $provider = new Libs\ObjectIterator;

        $handle = ReportCollection::createFromObject($provider);

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

    public function testFromArrayIterator()
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

        $handle = ReportCollection::createFromObject($provider);

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

    public function testFromObjectToArray()
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

        $handle = ReportCollection::createFromObject($provider);

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

    public function testFromOds()
    {
        $file = __DIR__ . '/../Files/table.ods';

        $handle = ReportCollection::createFromFile($file);

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

    public function testFromSlk()
    {
        $file = __DIR__ . '/../Files/table.slk';

        $handle = ReportCollection::createFromFile($file);

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

    public function testFromXls()
    {
        $file = __DIR__ . '/../Files/table.xls';

        $handle = ReportCollection::createFromFile($file);

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

    public function testFromXlsx()
    {
        $file = __DIR__ . '/../Files/table.xlsx';

        $handle = ReportCollection::createFromFile($file);

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

    public function testFromXml()
    {
        $file = __DIR__ . '/../Files/table.xml';

        $handle = ReportCollection::createFromFile($file);

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
