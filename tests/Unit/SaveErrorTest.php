<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaveErrorTest extends TestCase
{
    public function testSaveError()
    {
        $this->expectException(\InvalidArgumentException::class);

        $file = __DIR__ . '/../Files/table.xls';
        $saved_file = tempnam(sys_get_temp_dir(), 'report-collection') . '.err';

        $handle = \ReportCollection::createFromFile($file);
        $handle->save($saved_file);
    }
}
