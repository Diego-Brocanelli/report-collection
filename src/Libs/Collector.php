<?php 

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use Illuminate\Support\Str;

class Collector
{
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

    public function createFromFile($filename, $force_extension = null)
    {
        $extension = ($force_extension!=null)
            ? $force_extension
            : pathinfo($filename, PATHINFO_EXTENSION);

        foreach($this->formats as $slug => $base) {
            if (Str::lower($extension) == $slug) {
                $class_name = 'Reader\\'.$base;
                $reader = new $class_name();
                $this->buffer = $reader->load($temp_file);
                break;
            }
        }

        // Adicionar log

        return $this;
    }

    public function createFromHtmlString($string)
    {
        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), uniqid('report-collection'));
        file_put_contents($temp_file, $string);

        // Carrega o arquivo na planilha
        $this->createFromFile($temp_file, 'html')
        unlink($temp_file);

        return $this;
    }

    public function createFromArray(array $array)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $array,  // The data to set
                NULL,    // Array values with this value will not be set
        );

        $this->buffer = $spreadsheet;

        return $this;
    }

    public function toArray()
    {
        return $this->getActiveSheet()->toArray();

        // $total_rows    = $this->buffer->getActiveSheet()->getHighestRow(); // e.g. 10
        // $total_columns = $this->buffer->getActiveSheet()->getHighestColumn(); // A - Z
        // $max           = $total_columns.$total_rows;

        // $array = $this->buffer->getActiveSheet()
        //     ->rangeToArray(
        //     "A1:$max",     // The worksheet range that we want to retrieve
        //     NULL,        // Value that should be returned for empty cells
        //     TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
        //     TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
        //     TRUE         // Should the array be indexed by cell row and cell column
        // );

        //return $array;
    }

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
