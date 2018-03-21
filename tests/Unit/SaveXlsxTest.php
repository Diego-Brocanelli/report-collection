<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaveXlsxTest extends TestCase
{
    public function testSave()
    {
        $file = __DIR__ . '/../Files/table.xls';
        $handle = \ReportCollection::createFromFile($file);

        $saved_file = tempnam(sys_get_temp_dir(), 'report-collection') . '.xlsx';
        $handle->save($saved_file);

        $this->assertFileExists($saved_file);


        // Verifica o arquivo gerado
        $handle = \ReportCollection::createFromFile($saved_file);

        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(7, $array);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array[$x]);
        }

        $this->assertTrue(true);
    }
}
