<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class FormatFromXlsTest extends TestCase
{
    public function testFormatDots()
    {
        /*
        10.01.1980
        10-01-80
        10-01-1980
        10/01/80
        10/01/1980
        */
        $file = __DIR__ . '/../Files/format-date.xls';
        $handle = Reader::createFromXls($file);

        $handle->setInputDateFormat('d.m.y'); // 10.01.80
        $array = $handle->toArray();

        $date_object = $array[0][0];
        $this->assertEquals($date_object->format('d-m-Y'), '10-01-1980');

        $this->assertTrue(true);
    }

    public function testFormatBars()
    {
        $file = __DIR__ . '/../Files/format-date.xls';
        $handle = Reader::createFromXls($file);

        $handle->setInputDateFormat('d/m/Y'); // 10/01/1980
        $array = $handle->toArray();

        $date_object = $array[0][4];
        $this->assertEquals($date_object->format('d-m-Y'), '10-01-1980');

    }
}
