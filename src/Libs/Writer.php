<?php 

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;

class Writer
{
    /** @var ReportCollection\Libs\Reader */
    private $reader = null;

    /** @var array */
    private $buffer = null;

    /** @var string */
    private $title = 'Report Document';

    /** @var string */
    private $extension = null;
    
    /** @var array */
    private $writers = [
        'csv'      => 'Csv',
        'htm'      => 'Html',
        'html'     => 'Html',
        'ods'      => 'Ods',
        'pdf'      => 'Pdf',
        'xls'      => 'Xls',
        'xlsx'     => 'Xlsx',
        'xml'      => 'Xml'
    ];

    private $output_format_date = 'd/m/Y';

    /**
     * Importa os dados a partir do Reader
     * 
     * @param array $array
     */
    public static function createFromReader(Reader $reader)
    {
        $instance = new self;
        $instance->reader = $reader;

        return $instance;
    }

    public function setOutputDateFormat($format)
    {
        $this->output_format_date = $format;
    }

    private function generateSpreadsheet()
    {
        //$this->reader->toArray();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $this->values,  // os dados a adicionar
                NULL     // itens com este valor não serão setados
            );
    }

    public function save($filename)
    {
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        return $this->output($extension, $basename);   
    }

    public function output($extension, $name = null)
    {
        $basename = $name == null ? Str::slug($this->title) : $name;

        // $this->save('php://output');

        $this->httpHeaders($name, $extension);

        $writer = null;
        foreach($this->writers as $slug => $base) {

            if (Str::lower($extension) == $slug && $slug == 'pdf') {

                IOFactory::registerWriter('CustomPDF', PDFWriter::class);
                $writer = IOFactory::createWriter($this->getSpreadsheetObject(), 'CustomPDF');
                $writer->save($filename);

            } elseif (Str::lower($extension) == $slug) {

                $writer = IOFactory::createWriter($this->getSpreadsheetObject(), $base);
                $writer->save($filename);
            }
        }

        if ($writer == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for writing. Use " . implode(',', $this->writers));
        }
    }

    private function httpHeaders($basename, $extension)
    {
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
    }
}
