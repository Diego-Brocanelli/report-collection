<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;

class Reader
{
    /** @var string */
    protected $type      = null;

    /** @var string */
    protected $extension = null;

    /** @var mixed */
    protected $buffer    = null;

    /** @var array */
    protected $data      = null;

    /** @var array */
    protected $readers = [
        'csv'      => 'Csv',
        'gnumeric' => 'Gnumeric',
        'htm'      => 'Html',
        'html'     => 'Html',
        'ods'      => 'Ods',
        'slk'      => 'Slk',
        'xls'      => 'Xls',
        'xlsx'     => 'Xlsx',
        'xml'      => 'Xml'
    ];

    protected $input_format_date = 'd/m/Y';

    /**
     * Importa os dados a partir de um array
     *
     * @param array $array
     */
    public static function createFromArray(array $array)
    {
        $instance = new self;
        $instance->type   = 'array';
        $instance->buffer = $array;

        return $instance;
    }

    /**
     * Importa os dados a partir de um objeto.
     * O objeto deve:
     *
     * Implementar o método toArray()
     * ou
     * Ser iterável
     * ou
     * Ser passível de conversão para array (via atributos)
     *
     * @param mixed $object
     */
    public static function createFromObject($object)
    {
        $instance = new self;

        $instance->buffer = null;

        // Objeto possui o método toArray
        if (method_exists($object, 'toArray')) {
            $array = $object->toArray();
        }
        // Objeto é Iterável
        elseif($object instanceof \Traversable) {
            $array = iterator_to_array($object);
        }
        // Objeto simples
        else {

            $properties = (array) $object;
            $array = [];
            foreach ($properties as $item) {
                $array[] = (array) $item;
            }

            if (count($array) == 0) {
                throw new \UnexpectedValueException("The object must have attributes or a toArray method or be an implementation of Iterator", 1);
            }
        }

        $instance->type   = 'array';
        $instance->buffer = $array;

        return $instance;
    }

    /**
     * Importa os dados a partir de um trecho de código html
     *
     * @param string $string
     */
    public static function createFromHtmlString($string)
    {
        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), uniqid('report-collection'));
        file_put_contents($temp_file, $string);

        // Carrega o arquivo na planilha
        $instance = self::createFromHtml($temp_file);
        unlink($temp_file);

        return $instance;
    }

    /**
     * Importa os dados a partir de um arquivo CSV.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromCsv($filename)
    {
        return self::createFromFile($filename, 'csv');
    }

    /**
     * Importa os dados a a partir de um arquivo Gnumeric.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromGnumeric($filename)
    {
        return self::createFromFile($filename, 'gnumeric');
    }

    /**
     * Importa os dados a a partir de um arquivo HTML.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromHtml($filename)
    {
        return self::createFromFile($filename, 'html');
    }

    /**
     * Importa os dados a a partir de um arquivo ODS.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromOds($filename)
    {
        return self::createFromFile($filename, 'ods');
    }

    /**
     * Importa os dados a a partir de um arquivo SLK.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromSlk($filename)
    {
        return self::createFromFile($filename, 'slk');
    }

    /**
     * Importa os dados a a partir de um arquivo XLS.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromXls($filename)
    {
        return self::createFromFile($filename, 'xls');
    }

    /**
     * Importa os dados a a partir de um arquivo XLSX.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromXlsx($filename)
    {
        return self::createFromFile($filename, 'xlsx');
    }

    /**
     * Importa os dados a a partir de um arquivo XML.
     *
     * @param string $filename Caminho completo até o arquivo
     */
    public static function createFromXml($filename)
    {
        return self::createFromFile($filename, 'xml');
    }

    /**
     * Importa os dados a a partir de um arquivo.
     * As extensões suportadas são:
     * csv, gnumeric, htm, html, ods, slk, xls, xlsx e xml
     *
     * @param string $filename Arquivo e caminho completo
     * @param string force_extension para arquivos sem extensão
     */
    public static function createFromFile($filename, $force_extension = null)
    {
        $instance = new self;

        $instance->buffer = null;

        $extension = ($force_extension!=null)
            ? $force_extension
            : pathinfo($filename, PATHINFO_EXTENSION);

        $spreadsheet = null;

        foreach($instance->readers as $slug => $base) {

            if (Str::lower($extension) == $slug) {

                $class_name = 'PhpOffice\\PhpSpreadsheet\\Reader\\'.$base;
                $reader = new $class_name();
                $spreadsheet = $reader->load($filename);
                break;
            }
        }

        if ($spreadsheet == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for reading. Use " . implode(',', $instance->readers));
        }

        $instance->type      = 'spreadsheet';
        $instance->extension = $extension;
        $instance->buffer    = $spreadsheet;

        return $instance;
    }

    public function setInputDateFormat($format)
    {
        $this->input_format_date = $format;
    }

    private function extractDataFromSpreadsheet(Spreadsheet $sheet, $extension)
    {
        $headers   = [];
        $extracted = [];

        // Linhas
        $count_rows = 0;
        foreach ($sheet->getActiveSheet()->getRowIterator() as $index => $row) {

            $cell_iterator = $row->getCellIterator();
            $cell_iterator->setIterateOnlyExistingCells(false);

            $item    = [];
            $nulleds = [];

            // Colunas
            $count_cols = 0;
            foreach ($cell_iterator as $cell) {

                //$column = $cell->getColumn();
                $column = $count_cols++;

                $value = $cell->getValue();

                // Colunas de data serão retornadas como timestamp
                if (ExcelDate::isDateTime($cell) == true) {

                    $timezone = date_default_timezone_get();
                    $value = ExcelDate::excelToDateTimeObject($value, $timezone);

                } elseif(is_string($value)) {

                    $parsed = \DateTime::createFromFormat($this->input_format_date, $value);
                    if ($parsed !== false) {
                        $value = $parsed;
                    }
                }

                // Identifica os cabeçalhos e pula a linha
                if ($count_rows == 0) {

                    if (empty($value)) {
                        continue;
                    }
                    $headers[$column] = $value;
                    continue;
                }

                // Apenas as colunas identificadas do cabeçalho
                // serão analizadas e tratadas
                if (!isset($headers[$column])) {
                    continue;
                }

                // Colunas nulas não serão formatadas
                if (empty($value)) {
                    $nulleds[$column] = null;
                }

                $item[$column] = $value;

            } // Colunas

            // Se a linha inteira for nula,
            // termina o loop de verificação
            if (count($nulleds) == count($headers)) {
                break;
            }

            if ($count_rows != 0) {
                $extracted[$count_rows] = $item;
            }

            $count_rows++;

        } // Linhas

        return array_merge([$headers], $extracted);

    }

    /**
     * Devolve os dados em forma de array.
     *
     * @return array
     */
    public function toArray()
    {
        if($this->data !== null) {
            return $this->data;
        }

        if ($this->type == 'spreadsheet') {
            $this->data = $this->extractDataFromSpreadsheet($this->buffer, $this->extension);

        } else {
            $this->data = $this->buffer;
        }

        return $this->data;
    }

    /**
     * Devolve os dados em formato XML.
     *
     * @return string
     */
    public function toXml()
    {
        $data = $this->toArray();

        $writer = new \SimpleXMLElement('<Table/>');
        $headers = [];
        foreach ($data as $index => $item) {

            if ($index==0) {
                $headers = $item;
            }
            $child = $writer->addChild('Row');
            foreach ($headers as $k => $name) {
                $child->addChild('Cell', $item[$k]);
            }
        }

        $dom = dom_import_simplexml($writer)->ownerDocument;
        $dom->formatOutput = true;
        // $dom->save($filename);
        return $dom->saveXML();
    }
}
