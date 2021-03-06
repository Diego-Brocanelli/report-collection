<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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

    /** @var string */
    protected $input_format_date = null;

    /**
     * Importa os dados a partir de um array
     *
     * @param array $array
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromArray(array $array) : Reader
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;
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
     * @param object $object
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromObject(object $object) : Reader
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;

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
     * Importa os dados a a partir de um arquivo.
     * As extensões suportadas são:
     * csv, gnumeric, htm, html, ods, slk, xls, xlsx e xml
     *
     * @param string $filename Arquivo e caminho completo
     * @param string force_extension para arquivos sem extensão
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromFile(string $filename, $force_extension = null) : Reader
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;

        $instance->buffer = null;

        $extension = ($force_extension!=null)
            ? $force_extension
            : pathinfo($filename, PATHINFO_EXTENSION);

        $spreadsheet = null;

        foreach($instance->readers as $slug => $base) {

            if (strtolower($extension) == $slug) {

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

    /**
     * Importa os dados a partir de um arquivo CSV.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromCsv(string $filename) : Reader
    {
        return self::createFromFile($filename, 'csv');
    }

    /**
     * Importa os dados a a partir de um arquivo Gnumeric.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromGnumeric(string $filename) : Reader
    {
        return self::createFromFile($filename, 'gnumeric');
    }

    /**
     * Importa os dados a a partir de um arquivo HTML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromHtml(string $filename) : Reader
    {
        return self::createFromFile($filename, 'html');
    }

    /**
     * Importa os dados a partir de um trecho de código html
     *
     * @param string $string
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromHtmlString(string $html) : Reader
    {
        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), uniqid('report-collection'));
        file_put_contents($temp_file, $html);

        // Carrega o arquivo na planilha
        $instance = self::createFromHtml($temp_file);
        unlink($temp_file);

        return $instance;
    }

    /**
     * Importa os dados a a partir de um arquivo ODS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromOds(string $filename) : Reader
    {
        return self::createFromFile($filename, 'ods');
    }

    /**
     * Importa os dados a a partir de um arquivo SLK.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromSlk(string $filename) : Reader
    {
        return self::createFromFile($filename, 'slk');
    }

    /**
     * Importa os dados a a partir de um arquivo XLS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromXls(string $filename) : Reader
    {
        return self::createFromFile($filename, 'xls');
    }

    /**
     * Importa os dados a a partir de um arquivo XLSX.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromXlsx(string $filename) : Reader
    {
        return self::createFromFile($filename, 'xlsx');
    }

    /**
     * Importa os dados a a partir de um arquivo XML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Reader
     */
    public static function createFromXml(string $filename) : Reader
    {
        return self::createFromFile($filename, 'xml');
    }

    /**
     * Especifica o formato dos valores que deverão ser tratados como data.
     * O formato deve ser uma string com o código da formatação.
     * Ex: O formato d/m/Y resultará em 31/12/9999.
     * Caso um formato não seja especificado, a biblioteca tentará
     * detectar a data automaticamente.
     * @see https://secure.php.net/manual/pt_BR/datetime.createfromformat.php
     * @param string $format
     * @return ReportCollection\Libs\Reader
     */
    public function setInputDateFormat(string $format) : Reader
    {
        $this->input_format_date = $format;
        return $this;
    }

    /**
     * Devolve o buffer usado para o parseamento dos dados.
     * @return mixed
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Devolve os dados em forma de array.
     *
     * @return array
     */
    public function toArray() : array
    {
        if($this->data !== null) {
            return $this->data;
        }

        if ($this->type == 'spreadsheet') {
            $this->data = $this->parseDataFromSpreadsheet($this->buffer, $this->extension);

        } else {
            $this->data = $this->parseDataFromArray($this->buffer);
        }

        return $this->data;
    }

    /**
     * Devolve os dados em formato XML.
     *
     * @return string
     */
    public function toXml() : string
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

    private function parseDataFromSpreadsheet(Spreadsheet $sheet, $extension)
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
                $timezone = date_default_timezone_get();

                if (empty($value) == false && ExcelDate::isDateTime($cell) == true) {

                    // Colunas de data serão retornadas como timestamp
                    $value = ExcelDate::excelToDateTimeObject($value, $timezone);

                } elseif(empty($value) == false && is_string($value)) {

                    $value = trim($value);
                    $timezone = date_default_timezone_get();

                    // 1ª Verificação:
                    // O formato pode ser forçado $this->input_format_date != null
                    // ou automático $this->input_format_date == null
                    // A verificação automática busca pelas datas alternativas:
                    // 10.01.80, 10.01.1980, 10-01-80, 10-01-1980, 1980-01-10, 10/01/80, 10/01/80
                    $date = DateParser::parse($value, $this->input_format_date, $timezone);
                    if ($date !== false) {
                        $value = $date->getDateObject();
                    } elseif($this->input_format_date !== null) {
                        // 2ª Verificação:
                        // O formato da 1ª tentativa foi forçado
                        // nesta, tenta como automático $this->input_format_date == null
                        $date = DateParser::parse($value, null, $timezone);
                        if ($date !== false) {
                            $value = $date->getDateObject();
                        }
                    }
                }

                // Identifica os cabeçalhos e pula a linha
                if ($count_rows == 0) {

                    if ($value !== false && empty($value)) {
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

    private function parseDataFromArray(array $data)
    {
        foreach($data as $row => $cols ) {

            foreach ($cols as $col => $value) {

                if(empty($value) == false && is_string($value)) {

                    $value = trim($value);
                    $timezone = date_default_timezone_get();

                    // 1ª Verificação:
                    // O formato pode ser forçado $this->input_format_date != null
                    // ou automático $this->input_format_date == null
                    // A verificação automática busca pelas datas alternativas:
                    // 10.01.80, 10.01.1980, 10-01-80, 10-01-1980, 1980-01-10, 10/01/80, 10/01/80
                    $date = DateParser::parse($value, $this->input_format_date, $timezone);
                    if ($date !== false) {
                        $value = $date->getDateObject();
                    } elseif($this->input_format_date !== null) {
                        // 2ª Verificação:
                        // O formato da 1ª tentativa foi forçado
                        // nesta, tenta como automático $this->input_format_date == null
                        $date = DateParser::parse($value, null, $timezone);
                        if ($date !== false) {
                            $value = $date->getDateObject();
                        }
                    }

                    $data[$row][$col] = $value;
                }
            }
        }
        return $data;
    }
}
