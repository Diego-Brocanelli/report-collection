<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;

class WriterTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Country"],
        ["Alfreds Futterkiste", "Maria Anders", "Germany"],
        ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
        ["Ernst Handel", "Roland Mendel", "Austria"],
    );

    public function testGetColumVowel()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Libs\WriterAccessor::createFromReader($reader);

        // O cálculo da numeração resultante é baseana no modo excel, começando com 1
        $this->assertEquals('A', $writer->accessGetColumnVowel(1));
        $this->assertEquals('B', $writer->accessGetColumnVowel(2));
        $this->assertEquals('C', $writer->accessGetColumnVowel(3));
    }

    public function testGetColumVowelExceptionOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $writer = Libs\WriterAccessor::createFromReader($reader);

        // Apenas numeros são covertidos para vogal
        $writer->accessGetColumnVowel('A');
    }

    public function testGetColumVowelExceptionTwo()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = Reader::createFromArray($this->provider);
        $writer = Libs\WriterAccessor::createFromReader($reader);

        // O indice de vogais deve começar com 1
        $writer->accessGetColumnVowel(0);
    }

    public function testWriteColumnsWidths()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Libs\WriterAccessor::createFromReader($reader);
        // setado através de vogal
        $writer->setColumnWidth('A', 50);
        $writer->setColumnWidth('B', 30);
        $writer->setColumnWidth('C', 20);
        $writer->setColumnWidth('D', 10);

        $this->assertEquals(50, $writer->accessGetColumnWidth('A'));
        $this->assertEquals(30, $writer->accessGetColumnWidth('B'));
        $this->assertEquals(20, $writer->accessGetColumnWidth('C'));
        $this->assertEquals(10, $writer->accessGetColumnWidth('D'));

        $writer->accessCalcColumnWidth('A', 'sss'); // 3 caracteres
        $this->assertEquals(50, $writer->accessGetColumnWidth('A')); // permanece o 50 setado

        $writer->accessCalcColumnWidth('A', str_repeat('x', 100)); // 100 caracteres
        $this->assertEquals(105, $writer->accessGetColumnWidth('A')); // aumenta automaticamente

        $writer->accessCalcColumnWidth('A', 'dddddd'); // 6 caracteres
        $this->assertEquals(105, $writer->accessGetColumnWidth('A')); // permanece o 105 calculado anteriormente
    }
}
