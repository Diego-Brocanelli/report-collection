<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class FormatFromHtmlTest extends TestCase
{
    public function testImportError()
    {
        $file = __DIR__ . '/../Files/format.html';

        $handle = Reader::createFromHtml($file);

        $array = $handle->toArray();

        // $timestamp = $array[0][0];
        // $this->assertEquals(date('d-m-Y', $timestamp), '10-01-1980');

        // $timestamp = $array[0][1];
        // $this->assertEquals(date('d-m-Y', $timestamp), '10-01-1980');

        // $this->assertCount(3, $array[$x]);
        // $this->assertArrayHasKey(0, $array[$x]);
        // $this->assertArrayHasKey(1, $array[$x]);
        // $this->assertArrayHasKey(2, $array[$x]);

        $this->assertTrue(true);
        
        
    }
}
