<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaveXmlTest extends TestCase
{
    public function testSave()
    {
        $file = __DIR__ . '/../Files/table.xls';
        $handle = \ReportCollection::createFromFile($file);

        $saved_file = tempnam(sys_get_temp_dir(), 'report-collection') . '.xml';
        $handle->save($saved_file);

        $this->assertFileExists($saved_file);


        // Verifica o arquivo gerado
        $array = (array) simplexml_load_file($saved_file);

        $this->assertTrue(is_array($array['Row']));
        $this->assertCount(7, $array['Row']);

        for($x=0; $x<7; $x++) {
            $this->assertCount(3, $array['Row'][$x]);
        }
    }
}
