<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
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
    public function testParseAuto()
    {
        $unix = strtotime('1980-01-10');
        $excel = ExcelDate::timestampToExcel($unix);

        // timestamp: 10/01/1980 no php
        $date = DateParser::parse($unix);
        $this->assertEquals(DateParser::getDebug()['type'], 'unixtimestamp');
        $this->assertEquals(DateParser::getDebug()['original'], $unix);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals(DateParser::getDebug()['error'], null);

        // serial: 10/01/1980 no excel
        $parsed = DateParser::parse($excel);
        $this->assertEquals(DateParser::getDebug()['type'], 'exceltimestamp');
        $this->assertEquals(DateParser::getDebug()['original'], $excel);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals(DateParser::getDebug()['error'], null);

        // String separada com pontos
        $parsed = DateParser::parse('10.01.1980');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10.01.1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals(DateParser::getDebug()['error'], null);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String separada com traços
        $parsed = DateParser::parse('10-01-1980');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10-01-1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals(DateParser::getDebug()['error'], null);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String separada com barras
        $parsed = DateParser::parse('10/01/1980');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10/01/1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], '10-01-1980');
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals(DateParser::getDebug()['error'], null);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String inválida com pontos
        $parsed = DateParser::parse('10.01.80');
        $this->assertFalse($parsed);
        $this->assertNotEquals(DateParser::getDebug()['error'], null);

        // String inválida com tracos
        $parsed = DateParser::parse('10-01-80');
        $this->assertFalse($parsed);
        $this->assertNotEquals(DateParser::getDebug()['error'], null);

        // String inválida com barras
        $parsed = DateParser::parse('10/01/80');
        $this->assertFalse($parsed);
        $this->assertNotEquals(DateParser::getDebug()['error'], null);

        // String inválida
        $parsed = DateParser::parse('sss1010sasa0x');
        $this->assertFalse($parsed);
        $this->assertNotEquals(DateParser::getDebug()['error'], null);
    }

    public function testParseFormat()
    {
        $unix = \DateTime::createFromFormat('d-m-Y', '10-01-1980', new \DateTimeZone('UTC'))->format('U');

        // String separada com pontos
        $parsed = DateParser::parse('10.01.1980', 'd.m.Y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10.01.1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String separada com traços
        $parsed = DateParser::parse('10-01-1980', 'd-m-Y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10-01-1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String separada com barras
        $parsed = DateParser::parse('10/01/1980', 'd/m/Y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10/01/1980');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String inválida com pontos
        $parsed = DateParser::parse('10.01.80', 'd.m.y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10.01.80');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String inválida com tracos
        $parsed = DateParser::parse('10-01-80', 'd-m-y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10-01-80');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String inválida com barras
        $parsed = DateParser::parse('10/01/80', 'd/m/y');
        $this->assertTrue($parsed !== false);
        $this->assertEquals(DateParser::getDebug()['type'], 'string');
        $this->assertEquals(DateParser::getDebug()['original'], '10/01/80');
        $this->assertEquals(DateParser::getDebug()['fixed'], null);
        $this->assertEquals(DateParser::getDebug()['returned'], $unix);
        $this->assertEquals($parsed->getTimestamp(), $unix);

        // String inválida (isso será interpretado erroneamente como excel timestamp!!)
        // para solucionar, é preciso identificar um serial do excel!!!
        $parsed = DateParser::parse('100180', 'dmy');
        $this->assertTrue($parsed !== false);
    }
}
