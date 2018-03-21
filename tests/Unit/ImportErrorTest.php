<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportErrorTest extends TestCase
{
    public function testImportError()
    {
        $this->expectException(\InvalidArgumentException::class);

        $file = __DIR__ . '/../Files/table.err';

        \ReportCollection::createFromFile($file);
    }
}
