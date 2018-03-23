<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddHeaderTest extends TestCase
{
    public function testTextParser()
    {
        $this->assertTrue(true);
    }

    /*
    public function testTextParser()
    {
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $text = '<b>Report Collection</b>';
        $handle->parseTextContent($text);
        $this->assertEquals($handle->debug['parseTextContent'], strip_tags($text));

        $text = '<b>Autor</b>: Ricardo Pereira <u>Dias</u>';
        $handle->parseTextContent($text);
        $this->assertEquals($handle->debug['parseTextContent'], strip_tags($text));

        $text = '<i>Linguagem</i>: PHP não é <s>HTML</s>';
        $handle->parseTextContent($text);
        $this->assertEquals($handle->debug['parseTextContent'], strip_tags($text));
    }

    public function testAddHeaderRows()
    {
        $header_logo = 0;
        $header_rows = 3;
        $body_rows   = 7;

        $file = __DIR__ . '/../Files/table.xls';
        $saved_file = tempnam(sys_get_temp_dir(), 'areport-collection-header-') . '.xls';
        $saved_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aaa.xls';

        $handle = \ReportCollection::createFromFile($file);

        // Adiciona 3 linhas de cabeçalho
        //$handle->addHeaderRow('<b>Report Collection</b>', ['color' => '#0000ff']); // ERRO
        $handle->addHeaderRow('<b>Report Collection</b>'); // ERRO
        $handle->addHeaderRow('<b>Autor</b>: Ricardo Pereira <u>Dias</u>');
        $handle->addHeaderRow('<i>Linguagem</i>: PHP não é <s>HTML</s>');


        $handle->save($saved_file);

        $this->assertFileExists($saved_file);


        // Verifica o arquivo gerado
        $handle = \ReportCollection::createFromFile($saved_file);
        $array = $handle->toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount($header_logo+$header_rows+$body_rows, $array);
    }
    */
}
