<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportXlsxTest extends TestCase
{
    public function testImportFile()
    {
        $file = __DIR__ . '/../Files/table.xlsx';

        $handle = \ReportCollection::createFromFile($file);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }

        $this->assertTrue(true);
    }
}
