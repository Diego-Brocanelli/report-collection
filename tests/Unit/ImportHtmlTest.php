<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportHtmlTest extends TestCase
{
    public function testImportHtmlFile()
    {
        $file = __DIR__ . '/../Files/table.html';

        $handle = \ReportCollection::createFromFile($file);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportHtmFile()
    {
        $file = __DIR__ . '/../Files/table.htm';

        $handle = \ReportCollection::createFromFile($file);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }

    public function testImportHtmlString()
    {
        $contents = file_get_contents(__DIR__ . '/../Files/table.html');

        $handle = \ReportCollection::createFromHtmlString($contents);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }
    }
}
