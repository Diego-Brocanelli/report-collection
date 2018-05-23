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

    /** @var PhpOffice\PhpSpreadsheet\Spreadsheet */
    private $spreadsheet = null;

    /** @var array */
    private $writers = [
        'csv'      => 'Csv',
        'htm'      => 'Html',
        'html'     => 'Html',
        'ods'      => 'Ods',
        'pdf'      => 'Pdf',
        'xls'      => 'Xls',
        'xlsx'     => 'Xlsx',
        // 'xml'      => 'Xml'
        // 'blade'      => 'Blade'
    ];

    /** @var string */
    protected $output_format_date = 'd/m/Y';

    /** @var array */
    private $line_heights = [];

    /** @var array */
    private $columns_widths = [];

    /** @var array */
    private $info = [
        'creator'       => 'Report Collection',
        'last_modified' => 'Ricardo Pereira <https://rpdesignerfly.github.io>',
        'title'         => 'Documento de Relatório',
        'subject'       => 'Relatório de feito com Report Collection',
        'description'   => 'Este documento foi gerado usando a biblioteca Report Collection, deselvolvida por Ricardo Pereira Dias, que pode ser encontrada em https://github.com/rpdesignerfly/report-collection',
        'keywords'      => 'office 2007 openxml php',
        'category'      => 'Relatórios',
    ];

    /**
     * Importa os dados a partir do Reader
     *
     * @param ReportCollection\Libs\Reader $reader
     * @return ReportCollection\Libs\Writer
     */
    public static function createFromReader(Reader $reader) : Writer
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;
        $instance->reader = $reader;

        return $instance;
    }

    /**
     * Importa os dados a partir do Styler
     *
     * @param ReportCollection\Libs\Styler $styler
     * @return ReportCollection\Libs\Writer
     */
    public static function createFromStyler(Styler $styler) : Writer
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;
        $instance->styler = $styler;
        $instance->reader = $styler->getReader();

        return $instance;
    }

    /**
     *  Devolve a instancia do Reader.
     * @return ReportCollection\Libs\Reader
     */
    public function getReader() : Reader
    {
        return $this->reader;
    }

    /**
     *  Devolve a instancia do Styler.
     * @return ReportCollection\Libs\Styler
     */
    public function getStyler() : Styler
    {
        if ($this->styler == null) {
            $this->styler = Styler::createFromReader($this->getReader());
        }

        return $this->styler;
    }

    /**
     *  Devolve os dados estruturados para estilização.
     *
     * @return array
     */
    public function getBuffer() : array
    {
        $this->getStyler()->getBuffer();
    }

    /**
     * Seta a informação de criador do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoCreator(string $text) : Writer
    {
        $this->info['creator'] = $text;
        return $this;
    }

    /**
     * Seta a última pessoa a alterar o documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoLastModifiedBy(string $text) : Writer
    {
        $this->info['last_modified'] = $text;
        return $this;
    }

    /**
     * Seta o título do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoTitle(string $text) : Writer
    {
        $this->info['title'] = $text;
        return $this;
    }

    /**
     * Seta o assunto do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoSubject(string $text) : Writer
    {
        $this->info['subject'] = $text;
        return $this;
    }

    /**
     * Seta a descrição do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoDescription(string $text) : Writer
    {
        $this->info['description'] = $text;
        return $this;
    }

    /**
     * Seta palavras chave para o documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoKeywords(string $text) : Writer
    {
        $this->info['keywords'] = $text;
        return $this;
    }

    /**
     * Seta a categoria do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Writer
     */
    public function setInfoCategory(string $text) : Writer
    {
        $this->info['category'] = $text;
        return $this;
    }

    /**
     * Especifica o formato das datas no arquivo resultante da gravação.
     * O formato deve ser uma string com o código da formatação.
     * Ex: O formato d/m/Y resultará em 31/12/9999
     *
     * @see https://secure.php.net/manual/pt_BR/datetime.createfromformat.php
     * @param string $format
     * @return ReportCollection\Libs\Writer
     */
    public function setOutputDateFormat(string $format) : Writer
    {
        $this->output_format_date = $format;
        return $this;
    }

    /**
     * Define a largura padrão de uma coluna.
     * O valor de $col pode ser especificado como vogal (no estilo excel)
     * ou como índice numérico (começando com 0)
     *
     * @param mixed $col
     * @param int $value
     * @throws \InvalidArgumentException
     * @return ReportCollection\Libs\Writer
     */
    public function setColumnWidth($col, $value) : Writer
    {
        if (is_numeric($col)) {
            throw new \InvalidArgumentException("Unsupported column vowel");
        }
        $this->columns_widths[$col] = (int) $value;
        return $this;
    }

    /**
     * Grava o resultado em arquivo.
     *
     * @param  string  $filename
     * @param  boolean $force_download
     * @return void
     */
    public function save(string $filename, bool $force_download = false)
    {
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $factory = null;
        foreach($this->writers as $slug => $writer) {

            if ($extension == $slug && $slug == 'pdf') {
                IOFactory::registerWriter('CustomPDF', PDFWriter::class);
                $factory = IOFactory::createWriter($this->getSpreadsheet(), 'CustomPDF');

            } elseif ($extension == $slug && in_array($slug, ['html', 'htm']) == true) {
                IOFactory::registerWriter('CustomHtml', HtmlWriter::class);
                $factory = IOFactory::createWriter($this->getSpreadsheet(), 'CustomHtml');

            } elseif ($extension == $slug) {
                $factory = IOFactory::createWriter($this->getSpreadsheet(), $writer);
            }
        }

        if ($factory == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for writing. Use " . implode(',', $this->writers));
        }

        if ($force_download == true) {
            $this->httpHeaders($basename, $extension);
            $factory->save('php://output');
        } else {
            $factory->save($filename);
        }
    }

    /**
     * Libera o buffer e força o download.
     *
     * @param string $filename
     * @return void
     */
    public function output(string $filename)
    {
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        $this->save($basename, true);
    }

    private function getSpreadsheet() : Spreadsheet
    {
        if ($this->spreadsheet != null) {
            return $this->spreadsheet;
        }
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator($this->info['creator'])
            ->setLastModifiedBy($this->info['last_modified'])
            ->setTitle($this->info['title'])
            ->setSubject($this->info['subject'])
            ->setDescription($this->info['description'])
            ->setKeywords($this->info['keywords'])
            ->setCategory($this->info['category']);

        $this->spreadsheet->setActiveSheetIndex(0);

        $buffer = $this->getStyler()->getBuffer();

        $line_heights = [];
        foreach ($buffer as $row => $cols) {
            $line = $row+1;
            foreach ($cols as $col => $cell) {

                $column = $col+1;
                $contents   = $cell['value'];

                if ($contents instanceof \DateTime) {
                    $text = $contents->format($this->output_format_date);
                    // TODO: Formatar a célula como data
                } elseif (is_string($contents) || is_numeric($contents)) {
                    $text = $contents;
                }

                $styles = CssParser::parse($cell['styles']);

                $vowel = $this->getColumnVowel($column);
                // Calcula a largura da coluna
                $this->calcColumnWidth($vowel, $text);

                // Aplica o valor
                $this->spreadsheet->getActiveSheet()->getCell("{$vowel}{$line}")
                    ->setValue($text);

                $cell = $this->spreadsheet->getActiveSheet()->getStyle("{$vowel}{$line}");

                foreach($styles as $param => $value) {
                    switch($param) {
                        case 'background-color':
                            $cell->getFill()
                                ->setFillType($styles['background-fill'])
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
            $vowel = $this->getColumnVowel($col+1);
            $width = $this->getColumnWidth($vowel);
            $this->spreadsheet->getActiveSheet()
                ->getColumnDimension($vowel)->setWidth($width);
        }

        // Altura das linhas
        foreach ($this->line_heights as $line => $height) {
            $this->spreadsheet->getActiveSheet()
                ->getRowDimension($line)->setRowHeight($height);
        }

        return $this->spreadsheet;

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

    protected function calcLineHeight(int $line, int $int)
    {
        $height = $this->getLineHeight($line);
        if ($height < $int) {
            $this->line_heights[$line] = $int;
        }
    }

    protected function getLineHeight(int $line) : int
    {
        if(!isset($this->line_heights[$line])) {
            return 20;
        }
        return $this->line_heights[$line];
    }

    protected function calcColumnWidth(string $vowel, $text)
    {
        $width = $this->getColumnWidth($vowel);
        $int = \strlen($text) + 5;
        if ($width < $int) {
            $this->columns_widths[$vowel] = $int;
        }
    }

    protected function getColumnWidth(string $vowel) : int
    {
        if (is_numeric($vowel)) {
            throw new \InvalidArgumentException("Only vowels are accepted");
        }

        if(!isset($this->columns_widths[$vowel])) {
            $this->columns_widths[$vowel] = 5;
        }
        return $this->columns_widths[$vowel];
    }

    /**
     * Converte uma coluna numérica para as vogais correspondentes.
     * O indice numérico deve começar com 1.
     *
     * @param int $number
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getColumnVowel($number) : string
    {
        if (!is_int($number) && !is_numeric($number)) {
            throw new \InvalidArgumentException("Only numbers are accepted");
        }

        if (intval($number) == 0) {
            throw new \InvalidArgumentException("Unsupported number. Columns should start with index 1");
        }

        $number = (int) $number;

        // alfabeto
        $map = range('A', 'Z');
        $vowels = count($map);

        $number_one = (int) floor(($number-1)/$vowels);
        $vowel_one = $number_one>0 && isset($map[$number_one-1])
            ? $map[$number_one-1]
            : '';

        $number_two = $number - $vowels*$number_one;
        $vowel_two = isset($map[$number_two-1])
            ? $map[$number_two-1]
            : '';
        return $vowel_one . $vowel_two;
    }

    private function httpHeaders(string $basename, string $extension) : bool
    {
        if (php_sapi_name() == "cli") {
            // Quando em testes de unidade, não usa-se headers
            return false;
        }

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

        return true;
    }
}
