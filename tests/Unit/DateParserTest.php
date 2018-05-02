<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use ReportCollection\Libs\DateParser;
use ReportCollection\Tests\Libs;

class DateParserTest extends TestCase
{
    public function testCheckTimestamp()
    {
        $unix = strtotime('1980-01-10');
        $excel = ExcelDate::timestampToExcel($unix);

        $instance = DateParser::parse('10/01/1980');
        $this->assertTrue($instance->isUnixTimeStamp($unix));
        $this->assertFalse($instance->isUnixTimeStamp($excel));
    }
    public function testFormatDots()
    {
        $unix = strtotime('1980-01-10');
        $excel = ExcelDate::timestampToExcel($unix);

        // 10/01/1980 no php
        $date = DateParser::parse($unix);
        $this->assertEquals(DateParser::getDebug()['type'], 'timestamp');
        $this->assertEquals(DateParser::getDebug()['original'], $unix);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);

        // 10/01/1980 no excel
        $parsed = DateParser::parse($excel);
        $this->assertEquals(DateParser::getDebug()['type'], 'timestamp');
        $this->assertEquals(DateParser::getDebug()['original'], $excel);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);

       $parsed = DateParser::parse('10.01.1980');
       $this->assertTrue($parsed !== false);
       $this->assertEquals(DateParser::getDebug()['type'], 'timestamp');
       $this->assertEquals(DateParser::getDebug()['original'], '10.01.1980');
       $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
       $this->assertEquals(DateParser::getDebug()['returned'], $unix);
       $this->assertEquals($parsed->getTimestamp(), $unix);

       $parsed = DateParser::parse('10-01-1980');
       $this->assertTrue($parsed !== false);
       $this->assertEquals(DateParser::getDebug()['type'], 'timestamp');
       $this->assertEquals(DateParser::getDebug()['original'], '10-01-1980');
       $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
       $this->assertEquals(DateParser::getDebug()['returned'], $unix);
       $this->assertEquals($parsed->getTimestamp(), $unix);

       $parsed = DateParser::parse('10/01/1980');
       $this->assertTrue($parsed !== false);
       $this->assertEquals(DateParser::getDebug()['type'], 'timestamp');
       $this->assertEquals(DateParser::getDebug()['original'], '10/01/1980');
       $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
       $this->assertEquals(DateParser::getDebug()['returned'], $unix);
       $this->assertEquals($parsed->getTimestamp(), $unix);

       $parsed = DateParser::parse('10.01.80');
       $this->assertTrue($parsed !== false);

       $parsed = DateParser::parse('10-01-80');
       $this->assertTrue($parsed !== false);

       $parsed = DateParser::parse('10/01/80');
       $this->assertTrue($parsed !== false);

    }
}
