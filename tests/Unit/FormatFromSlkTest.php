<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class FormatFromSlkTest extends TestCase
{
    /*
    O Arquivo contém os seguintes formatos
    10.01.80	10.01.1980	10-01-80	10-01-1980	10/01/80	10/01/80
     */

    public function testForcedInvalidDots()
    {
        $file = __DIR__ . '/../Files/format-date.slk';

        $handle = Reader::createFromSlk($file);
        $handle->setInputDateFormat('d.m.y'); // força a detecção de 10.01.80
        $array = $handle->toArray();
        $this->assertEquals($array[0][0]->format('d-m-Y'), '10-01-1980'); // invalida 10.01.80
        $this->assertEquals($array[0][1]->format('d-m-Y'), '10-01-1980'); // 10.01.1980
        $this->assertEquals($array[0][2], '10-01-80');                     // invalida 10-01-80
        $this->assertEquals($array[0][3]->format('d-m-Y'), '10-01-1980'); // 10-01-1980

        // TODO O SLK salva a data como float... é preciso itentificar um serial do excel!!!
        $this->assertEquals($array[0][4], '29230.0'); // 10/01/80
        $this->assertEquals($array[0][5], '29230.0'); // 10/01/1980
    }

    public function testForcedInvalidDashs()
    {
        $file = __DIR__ . '/../Files/format-date.slk';

        $handle = Reader::createFromSlk($file);
        $handle->setInputDateFormat('d-m-y'); // força a detecção de 10-01-80
        $array = $handle->toArray();
        $this->assertEquals($array[0][0], '10.01.80');                     // invalida 10.01.80
        $this->assertEquals($array[0][1]->format('d-m-Y'), '10-01-1980'); // 10.01.1980
        $this->assertEquals($array[0][2]->format('d-m-Y'), '10-01-1980'); // invalida 10-01-80
        $this->assertEquals($array[0][3]->format('d-m-Y'), '10-01-1980'); // 10-01-1980

        // TODO O SLK salva a data como float... é preciso itentificar um serial do excel!!!
        $this->assertEquals($array[0][4], '29230.0'); // 10/01/80
        $this->assertEquals($array[0][5], '29230.0'); // 10/01/1980
    }
}
