<?php 

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use Illuminate\Support\Str;

class Collector
{
    private static $instance = null;

    private $buffer = null;

    private $title = 'Report Document';

    private $formats = [
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

    public function getSpreadsheetObject()
    {
        return $this->buffer;
    }

    public function getActiveSheet()
    {
        return $this->getSpreadsheetObject()->getActiveSheet();
    }

    /**
     * Ex: 10
     */
    public function getLastRow() 
    {
        return $this->getActiveSheet()->getHighestRow();
    }

    /**
     * Ex: H
     */
    public function getLastColumn() 
    {
        return $this->getActiveSheet()->getHighestColumn();
    }

    //
    // Métodos Construtores
    //

    /**
     * Cria uma planilha a partir de um arquivo.
     * As extensões suportadas são:
     * csv, gnumeric, htm, html, ods, slk, xls, xlsx e xml
     * 
     * @param string $filename Arquivo e caminho completo
     * @param string force_extension para arquivos sem extensão
     */
    public static function createFromFile($filename, $force_extension = null)
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        $extension = ($force_extension!=null)
            ? $force_extension
            : pathinfo($filename, PATHINFO_EXTENSION);

        self::$instance->buffer = null;

        foreach(self::$instance->formats as $slug => $base) {

            if (Str::lower($extension) == $slug) {

                $class_name = 'PhpOffice\\PhpSpreadsheet\\Reader\\'.$base;
                $reader = new $class_name();
                self::$instance->buffer = $reader->load($filename);
                break;
            }
        }

        return self::$instance;
    }

    /**
     * Cria uma planilha a partir de código html
     * 
     * @param string $string
     */
    public static function createFromHtmlString($string)
    {
        self::$instance = new self;

        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), uniqid('report-collection'));
        file_put_contents($temp_file, $string);

        // Carrega o arquivo na planilha
        self::$instance->createFromFile($temp_file, 'html');
        unlink($temp_file);

        return self::$instance;
    }

    public static function createFromArray(array $array)
    {
        self::$instance = new self;

        self::$instance->buffer = null;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $array,  // os dados a adicionar
                NULL     // itens com este valor não serão setados
            );

        self::$instance->buffer = $spreadsheet;

        return self::$instance;
    }

    public static function createFromObject($object)
    {
        self::$instance = new self;

        self::$instance->buffer = null;

        if (method_exists($object, 'toArray')) {
            $array = $object->toArray();
        }
        elseif($object instanceof Iterator) {
            $array = iterator_to_array($object);

        }
        else {

            $array = (array) $object;
            foreach ($array as $k => $item) {
                $array[$k] = (array) $item;
            }

            // throw new LogicException("The object must have a toArray method or be an implementation of Iterator", 1);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $array,  // os dados a adicionar
                NULL     // itens com este valor não serão setados
            );

        self::$instance->buffer = $spreadsheet;

        return self::$instance;
    }

    //
    // API
    //

    public function toArray()
    {
        // Planilhas do Gnumeric e Xlsx
        // possuem linha e colunas nulas na exportação de array
        // por isso, são removidas aqui

        $list = [];
        $array = $this->getActiveSheet()->toArray();

        // Linhas
        foreach($array as $row_id => $rows) {

            $line = array_filter($rows, function($v){ return !is_null($v); });
            if (!empty($line)) {
                $list[] = $line;
            }
        }

        return $list;
    }

    /**
     * Salva a planilha em um formato especifico.
     * As extensões suportadas são:
     * csv, html, ods, pdf, xls e xlsx
     * 
     * @param string $filename Arquivo e caminho completo
     * @param string force_extension para arquivos sem extensão
     */
    public function save($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $writer = null;
        foreach($this->formats as $slug => $base) {
            if (Str::lower($extension) == $slug) {
                $writer = IOFactory::createWriter($this->object, $base);
                $writer->save($filename);
            }
        }

        return $this;
    }

    public function download($filename)
    {
        $basename = pathinfo($filename, PATHINFO_BASENAME);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        // Cabeçalhos para MimeType
        switch(Str::lower($extension))
        {
            case 'csv':
                header('Content-Type: text/csv');
                break;

            case 'html':
                header('Content-Type: text/html');
                break;

            case 'ods':
                header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
                break;

            case 'pdf':
                header('Content-Type: application/pdf');
                break;

            case 'xls':
                header('Content-Type: application/vnd.ms-excel');
                break;

            case 'xlsx':
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                break;
        }

        // Cabeçalhos para Download
        header('Content-Disposition: attachment;filename="'.$basename.'"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1'); //IE 9
        
        // Para evitar cache no IE
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Data no passado
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Sempre modificado
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $this->save('php://output');
        exit;
    }
}