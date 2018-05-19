<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class CreateFromFileErrorTest extends TestCase
{
    public function testImportError()
    {
        $this->expectException(\InvalidArgumentException::class);

        $file = __DIR__ . '/../Files/table.err';

        Reader::createFromFile($file);
    }
}
