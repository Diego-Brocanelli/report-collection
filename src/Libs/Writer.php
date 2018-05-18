<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use ReportCollection\Libs\CssParser;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Writer
{
    /** @var ReportCollection\Libs\Reader */
    private $reader = null;

    /** @var ReportCollection\Libs\Styler */
    private $styler = null;

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

    /** @var array */
    private $line_heights = [];

    /** @var array */
    private $colums_widths = [];

    /**
     * Importa os dados a partir do Reader
     *
     * @param ReportCollection\Libs\Reader $reader
     */
    public static function createFromReader(Reader $reader)
    {
        $instance = new self;
        $instance->reader = $reader;

        return $instance;
    }

    /**
     * Importa os dados a partir do Styler
     *
     * @param ReportCollection\Libs\Styler $styler
     */
    public static function createFromStyler(Styler $styler)
    {
        $instance = new self;
        $instance->styler = $styler;
        $instance->reader = $styler->getReader();

        return $instance;
    }

    /**
     *  Devolve a instancia do Reader.
     * @return ReportCollection\Libs\Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     *  Devolve a instancia do Styler.
     * @return ReportCollection\Libs\Styler
     */
    public function getStyler()
    {
        if ($this->styler == null) {
            $this->styler = Styler::createFromReader($this->getReader());
        }

        return $this->styler;
    }

    /**
     *  Devolve os dados estruturados para estilização.
     * @return array
     */
    public function getBuffer()
    {
        $this->getStyler()->getBuffer();
    }

    private function generateSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Report Collection")
            ->setLastModifiedBy("Rocardo Pereira <>")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription(
                "Test document for Office 2007 XLSX, generated using PHP classes."
            )
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $spreadsheet->setActiveSheetIndex(0);

        $buffer = $this->getStyler()->getBuffer();

        $line_heights = [];
        foreach ($buffer as $row => $cols) {
            $line = $row+1;
            foreach ($cols as $col => $cell) {

                $column = $col+1;
                $text   = $cell['value'];
                $styles = CssParser::parse($cell['styles']);

                $vowel = $this->getColumnVowel($column);
                // Calcula a largura da coluna
                $this->calcColumnWidth($vowel, $text);

                // Aplica o valor
                $spreadsheet->getActiveSheet()->getCell("{$vowel}{$line}")
                    ->setValue($text);

                $cell = $spreadsheet->getActiveSheet()->getStyle("{$vowel}{$line}");

                foreach($styles as $param => $value) {
                    switch($param) {
                        case 'background-color':
                            $cell->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->setStartColor($value);
                            break;

                        case 'color':
                            $cell->getFont()->setColor($value);
                            break;

                        case 'font-face':
                            $cell->getFont()->setName($value);
                            break;

                        case 'font-size':
                            $cell->getFont()->setSize($value);
                            break;

                        case 'font-weight':
                            $cell->getFont()->setBold($value);
                            break;

                        case 'font-style':
                            $cell->getFont()->setItalic($value);
                            break;

                        case 'line-height':
                            $this->calcLineHeight($line, $value);
                            break;

                        case 'vertical-align':
                            $cell->getAlignment()->setVertical($value);
                            break;

                        case 'text-align':
                            $cell->getAlignment()->setHorizontal($value);
                            break;

                        case 'border-top-color':
                            $cell->getBorders()->getTop()->setColor($value);
                            break;

                        case 'border-right-color':
                            $cell->getBorders()->getRight()->setColor($value);
                            break;

                        case 'border-bottom-color':
                            $cell->getBorders()->getBottom()->setColor($value);
                            break;

                        case 'border-left-color':
                            $cell->getBorders()->getLeft()->setColor($value);
                            break;

                        case 'border-top-style':
                            $cell->getBorders()->getTop()->setBorderStyle($value);
                            break;

                        case 'border-right-style':
                            $cell->getBorders()->getRight()->setBorderStyle($value);
                            break;

                        case 'border-bottom-style':
                            $cell->getBorders()->getBottom()->setBorderStyle($value);
                            break;

                        case 'border-left-style':
                            $cell->getBorders()->getLeft()->setBorderStyle($value);
                            break;
                    }
                }
            }
        }

        // Largura das colunas
        foreach ($buffer[0] as $col => $nulled) {
            $vowel = $this->getColumnVowel($col);
            $width = $this->getColumnWidth($vowel);
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($vowel)->setWidth($width);
        }

        // Altura das linhas
        foreach ($this->line_heights as $line => $height) {
            $spreadsheet->getActiveSheet()
                ->getRowDimension($line)->setRowHeight($height);
        }

        return $spreadsheet;

        // $spreadsheet->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
        // $spreadsheet->getActiveSheet()->getPageSetup()->setVerticalCentered(false);

        // Seta uma data com base no unixtimestamp
        // $spreadsheet->getActiveSheet()
        //     ->setCellValue('D1', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($time));
        // $spreadsheet->getActiveSheet()->getStyle('D1')
        //     ->getNumberFormat()
        //     ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);

        // ----------------

        // Seta um valor explicitamente
        // $spreadsheet->getActiveSheet()->getCell('A1')
        //     ->setValueExplicit(
        //         '25',
        //         \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
        //     );

        // ----------------
        //
        // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing();
        // $drawing->setName('PhpSpreadsheet logo');
        // $drawing->setPath('./images/PhpSpreadsheet_logo.png');
        // $drawing->setHeight(36);
        // $spreadsheet->getActiveSheet()->getHeaderFooter()->addImage($drawing, \PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter::IMAGE_HEADER_LEFT);

        // ----------------

        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getFill()->getStartColor()->setARGB('FFFF0000');

        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);

        // ----------------

        // $spreadsheet->getActiveSheet()->getRowDimension('10')->setRowHeight(100);

        // $spreadsheet->getActiveSheet()->mergeCells('A18:E22');
        //
        // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        // $drawing->setName('Logo');
        // $drawing->setDescription('Logo');
        // $drawing->setPath('./images/officelogo.jpg');
        // $drawing->setHeight(36);

        // $spreadsheet->getActiveSheet()
        //     ->fromArray(
        //         $this->values,  // os dados a adicionar
        //         NULL     // itens com este valor não serão setados
        //     );
        //
        // $spreadsheet->getActiveSheet()
        //             ->getColumnDimension($vowel)->setWidth($col_width[$vowel]);
    }

    private function calcLineHeight($line, $int)
    {
        $height = $this->getLineHeight($line);
        if ($height < $int) {
            $this->line_heights[$line] = $int;
        }
    }

    private function getLineHeight($line)
    {
        if(!isset($this->line_heights[$line])) {
            return 20;
        }
        return $this->line_heights[$line];
    }

    private function calcColumnWidth($vowel, $text)
    {
        $width = $this->getColumnWidth($vowel);
        $int = \strlen($text) + 3;
        if ($width < $int) {
            $this->colums_widths[$vowel] = $int;
        }
    }

    private function getColumnWidth($vowel)
    {
        if(!isset($this->colums_widths[$vowel])) {
            $this->colums_widths[$vowel] = 5;
        }
        return $this->colums_widths[$vowel];
    }

    /**
     * Converte uma coluna numérica para as vogais correspondentes.
     *
     * @param int $number
     * @return string
     */
    protected function getColumnVowel($number)
    {
        if (!is_int($number) && !is_numeric($number)) {
            return $number;
        }

        $number = (int) $number;

        // alfabeto
        $map = range('A', 'Z');
        $vowels = count($map);

        $number_one = (int) floor(($number-1)/$vowels);
        $number_two = $number - $vowels*$number_one;

        $vowel_one = $number_one>0 && isset($map[$number_one-1])
            ? $map[$number_one-1]
            : '';

        $vowel_two = isset($map[$number_two-1])
            ? $map[$number_two-1]
            : $this->getLastColumn();
        return $vowel_one . $vowel_two;
    }

    public function save($filename, $download = false)
    {
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $factory = null;
        foreach($this->writers as $slug => $writer) {

            if ($extension == $slug && $slug == 'pdf') {
                IOFactory::registerWriter('CustomPDF', PDFWriter::class);
                $factory = IOFactory::createWriter($this->generateSpreadsheet(), 'CustomPDF');
            } elseif ($extension == $slug) {
                $factory = IOFactory::createWriter($this->generateSpreadsheet(), $writer);
            }
        }

        if ($factory == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for writing. Use " . implode(',', $this->writers));
        }

        if ($download == true) {
            $this->httpHeaders($basename, $extension);
            $factory->save('php://output');
        } else {
            $factory->save($filename);
        }
    }

    public function output($filename)
    {
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        $this->save($basename, true);
    }

    private function httpHeaders($basename, $extension)
    {
        // Cabeçalhos para MimeType
        switch(strtolower($extension))
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
