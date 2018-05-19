<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Writer;
use ReportCollection\Tests\Libs;

class WriterFromReaderTest extends TestCase
{
    private $provider = array(
        ["Company", "Contact", "Country"],
        ["Alfreds Futterkiste", "Maria Anders", "Germany"],
        ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
        ["Ernst Handel", "Roland Mendel", "Austria"],
    );

    public function testWriteCreate()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Writer::createFromReader($reader);
        $this->assertInstanceOf(Writer::class, $writer);
    }

    public function testWriteSave()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Writer::createFromReader($reader);

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterFromReaderSaveTest') . ".xls";
        $writer->save($temp_file);

        $this->assertFileExists($temp_file);

        // Verifica se o arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);
    }

    public function testWriteDownload()
    {
        $reader = Reader::createFromArray($this->provider);
        $writer = Writer::createFromReader($reader);

        $temp_file = tempnam(sys_get_temp_dir(), 'WriterFromReaderSaveTest') . ".xls";

        // Pega o conteúdo do download
        ob_start();
        $writer->output($temp_file);
        $download_contents = ob_get_contents();
        ob_end_clean();

        $this->assertFalse(file_exists($temp_file));
        // Grava um arquivo com o conteúdo do download
        \file_put_contents($temp_file, $download_contents);
        $this->assertFileExists($temp_file);

        // Verifica se o arquivo gravado está legível
        $reader = Reader::createFromFile($temp_file);
        $this->assertInstanceOf(Reader::class, $reader);
    }
}
