<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SavePdfTest extends TestCase
{
    public function testSave()
    {
        $file = __DIR__ . '/../Files/table.xls';
        $handle = \ReportCollection::createFromFile($file);

        $saved_file = tempnam(sys_get_temp_dir(), 'report-collection') . '.pdf';
        $handle->save($saved_file);

        $this->assertFileExists($saved_file);
    }
}
