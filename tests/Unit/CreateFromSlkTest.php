<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class ImportSlkTest extends TestCase
{
    public function testImportFile()
    {
        $file = __DIR__ . '/../Files/table.slk';

        $handle = Reader::createFromFile($file);

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
