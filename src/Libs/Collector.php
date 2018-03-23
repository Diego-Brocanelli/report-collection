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

    private $readers = [
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


    private $title = 'Report Document';

    private $header_logo = null;

    private $header_rows = [];

    private $default_styles = [
        'background-color-odd'  => '#ffffff',
        'background-color-even' => '#ffffff',
        'line-height'           => '25',
        
        'color'                 => '#ff0000',
        'font-face'             => 'Arial',
        'font-size'             => '12',
        'font-weight'           => 'normal',
        'font-style'            => 'normal',
        'vertical-align'        => 'middle',
        'text-align'            => 'left',
    ];

    private $header_styles = [
        
    ];

    private $body_styles = [
        
    ];

    public $debug = [];


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

        foreach(self::$instance->readers as $slug => $base) {

            if (Str::lower($extension) == $slug) {

                $class_name = 'PhpOffice\\PhpSpreadsheet\\Reader\\'.$base;
                $reader = new $class_name();
                self::$instance->buffer = $reader->load($filename);
                break;
            }
        }

        if (self::$instance->buffer == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for reading. Use " . implode(',', self::$instance->readers));
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

            $array = (array) $object;
            foreach ($array as $k => $item) {
                $array[$k] = (array) $item;
            }

            if (count($array) == 0) {
                throw new \UnexpectedValueException("The object must have attributes or a toArray method or be an implementation of Iterator", 1);
            }
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
    // Informações da planilha
    //

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

    private function getColumnVowel($number)
    {
        $num = intval($number)-1;
        $map = range('A', 'Z');
        // TODO: adicionar + alfabetos 'AA .. DA, DB'
        return isset($map[$num]) ? $map[$num] : $this->getLastColumn();
    }


    //
    // Cabeçalho da Planilha
    //

    public function setHeaderLogo($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException("FIle is not exists.");
        }

        $this->header_logo = $filename;
    }

    public function addHeaderRow($content, $colspan = 'auto', array $styles = [])
    {
        $this->header_rows[] = [
            'content' => $content,
            'colspan' => $colspan,
            'styles'  => $this->normalizeStyles($styles)
        ];
    }

    private function getHeaderNumRows()
    {
        return count($this->header_rows);
    }


    //
    // Aparencia da Planilha
    //

    public function normalizeStyles(array $styles)
    {
        $clean_styles = [];

        foreach($this->default_styles as $attr => $value) {

            $clean_styles[$attr] = isset($styles[$attr])
                ? $styles[$attr] 
                : $value;
        }

        return $clean_styles;
    }

    public function setStyles($target, array $styles)
    {
        if ($target == 'body') {
            $this->body_styles = $styles;
        } else {
            $this->header_styles = $styles;
        }

        return $this;
    }

    public function getStyles($target = null)
    {
        switch($target) {

            case 'header':
                return $this->normalizeStyles($this->header_styles);
                break;

            case 'body':
                return $this->normalizeStyles($this->body_styles);
                break;

            default:
                return $this->default_styles;
        }
    }

    public function setHeaderStyles(array $styles)
    {
        $this->setStyles('header', $styles);
        return $this;
    }

    public function setBodyStyles(array $styles)
    {
        $this->setStyles('body', $styles);
        return $this;
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


    //
    // Formatação
    //

    

    

    private function setupHeader()
    {
        if ($this->getHeaderNumRows() == 0) {
            return false;
        }

        $header_styles = $this->normalizeStyles($this->header_styles);

        foreach ($this->header_rows as $index => $row) {

            $line = $index+1;

            $this->getActiveSheet()->insertNewRowBefore($line, 1);

            $styles = count($row['styles'])>0 
                ? $row['styles'] 
                : $header_styles;


            $content = $this->parseTextContent($row['content'], $styles);
            $colspan = is_numeric($row['colspan']) 
                ? $this->getColumnVowel($row['colspan'])
                : $this->getLastColumn();

            $this->getActiveSheet()->mergeCells("A{$line}:{$colspan}{$line}");
            $this->getActiveSheet()->getCell("A{$line}")->setValue($content);

            $this->applyStyles($this->getActiveSheet()->getStyle("A{$line}"), $styles);
        }
    }

    public function parseTextContent($string, array $styles = [])
    {
        $styles = $this->normalizeStyles($styles);

        $string = "" . $string;

        // https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#add-rich-text-to-a-cell

        // Encontra as tags b, u, i
        $richs = [];
        preg_match_all("/<[b|u|i|s]>(.*?)<\/[b|u|i|s]>/", $string, $richs);

        // Separa o texto em partes
        $strips = [];
        $strip_styles = [];
        $last = '';
        foreach ($richs[0] as $index => $tagged) {

            $split = explode($tagged, $string);
            $strips[] = $split[0]; // adicona conteudo texto anterior à tag
            $strip_styles[] = 't';
            $strips[] = $richs[1][$index]; // adiciona conteudo da tag
            $strip_styles[] = substr($tagged, 1, 1);
            $string = $split[1]; // atualiza a string             
        }

        if (!empty($string)) {
            $strips[] = $string; // adicona conteudo texto anterior à tag
            $strip_styles[] = 't';
        }

        $text = new \PhpOffice\PhpSpreadsheet\RichText\RichText();

        $this->debug['parseTextContent'] = implode('', $strips);

        $hex = 'FF' . trim(strtoupper($styles['color']), '#');
        $color = new \PhpOffice\PhpSpreadsheet\Style\Color($hex);

        foreach ($strips as $k => $value) {

            switch($strip_styles[$k]) {
                case 't':
                    $t = $text->createTextRun($value);
                    $t->getFont()->setColor($color);
                    $t->getFont()->setName($styles['font-face']);
                    $t->getFont()->setSize($styles['font-size']);
                    break;
                case 'b':
                    $b = $text->createTextRun($value);
                    $b->getFont()->setBold(true);
                    $b->getFont()->setColor($color);
                    $b->getFont()->setName($styles['font-face']);
                    $b->getFont()->setSize($styles['font-size']);
                    break;
                case 'i':
                    $i = $text->createTextRun($value);
                    $i->getFont()->setItalic(true);
                    $i->getFont()->setColor($color);
                    $i->getFont()->setName($styles['font-face']);
                    $i->getFont()->setSize($styles['font-size']);
                    break;
                case 'u':
                    $u = $text->createTextRun($value);
                    $u->getFont()->setUnderline(true);
                    $u->getFont()->setColor($color);
                    $u->getFont()->setName($styles['font-face']);
                    $u->getFont()->setSize($styles['font-size']);
                    break;
                case 's':
                    $s = $text->createTextRun($value);
                    $s->getFont()->setStrikethrough(true);
                    $s->getFont()->setColor($color);
                    $s->getFont()->setName($styles['font-face']);
                    $s->getFont()->setSize($styles['font-size']);
                    break;
            }
        }

        return $text;
    }

    private function applyStyles($object, array $styles)
    {
        foreach ($styles as $param => $value) {

            switch($param) {

                // case 'background-color':
                //     $value = 'FF' . trim(strtoupper($value), '#');
                //     $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
                //     $style_range->getFill()
                //                 ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                //                 ->setStartColor($color);
                //     break;

                case 'color':
                    $value = 'FF' . trim(strtoupper($value), '#');
                    $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
                    $object->getFont()->setColor($color);
                    break;

                case 'font-face':
                    $object->getFont()->setName($value);
                    break;

                case 'font-size':
                    $object->getFont()->setSize($value);
                    break;

                case 'font-weight':
                    $object->getFont()->setBold($value=='bold');
                    break;

                case 'font-style':
                    $object->getFont()->setItalic($value=='italic');
                    break;

                // case 'border-style':
                //     $object->getBorders()->getLeft($value=='italic');
                //     break;

                case 'vertical-align':
                    switch($value) {
                        case 'top':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP;
                            break;
                        case 'middle':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
                            break;
                        case 'bottom':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM;
                            break;
                        default:
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
                    }
                    $object->getAlignment()->setVertical($align);
                    break;

                case 'text-align':
                    switch($value) {
                        case 'left':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
                            break;
                        case 'right':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
                            break;
                        case 'center':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
                            break;
                        case 'justify':
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY;
                            break;
                        default:
                            $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
                    }
                    $object->getAlignment()->setHorizontal($align);
                    break;
            }
        }
    }

    private function setupDefaultStyles()
    {
        $this->getActiveSheet()->getDefaultRowDimension()
            ->setRowHeight($this->default_styles['line-height']);

        $this->applyStyles($this->getSpreadsheetObject()->getDefaultStyle(), $this->default_styles);
    }

    private function setupHeaderStyles()
    {
        $rows = $this->getHeaderNumRows();
        if ($rows == 0) {
            return false;
        }

        $range = 'A1:' . $this->getLastColumn() . $rows;

        // Altura das linhas
        for($x=1; $x <= $rows; $x++) {
            $this->getActiveSheet()
                 ->getRowDimension($x)
                 ->setRowHeight($this->header_styles['line-height']);
        }

        //$this->applyStyles($this->getActiveSheet()->getStyle($range), $this->header_styles);


        $value = 'FF' . trim($this->header_styles['background-color-odd'], '#');
        $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
        $this->getActiveSheet()->getStyle($range)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->setStartColor($color);

        
    }

    private function setupBodyStyles()
    {
        $styles = $this->normalizeStyles($this->body_styles);

        $start_row = $this->getHeaderNumRows() + 1;
        $end_row   = $this->getLastRow();

        $range = 'A' . $start_row . ':' . $this->getLastColumn() . $end_row;

        $this->applyStyles($this->getActiveSheet()->getStyle($range), $styles);

        for($x=$start_row; $x <= $end_row; $x++) {

            $this->getActiveSheet()
                 ->getRowDimension($x)
                 ->setRowHeight($styles['line-height']);

            if ($x%2 == 0) {
                $value = $styles['background-color-odd'];
            }
            else {
                $value = $styles['background-color-even'];
            }

            $range_bg = 'A' . $x . ':' . $this->getLastColumn() . $x;

            // $value = 'FF' . trim(strtoupper($value), '#');
            // $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
            // $this->getActiveSheet()->getStyle($range)
            //      ->getFill()
            //      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            //      ->setStartColor($color);
        }
        
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
        $this->setupHeader();

        $this->setupDefaultStyles();

        $this->setupBodyStyles();

        $this->setupHeaderStyles();

        // Prepara para salvar

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $writer = null;
        foreach($this->writers as $slug => $base) {

            if (Str::lower($extension) == $slug && $slug == 'pdf') {

                $writer = IOFactory::createWriter($this->getSpreadsheetObject(), 'Mpdf');
                $writer->save($filename);

            }
            elseif (Str::lower($extension) == $slug && $slug == 'xml') {

                // $writer = IOFactory::createWriter($this->getSpreadsheetObject(), 'Html');
                // $data = explode('</style>', $writer->generateSheetData())[1];
                // $data = preg_replace('#<col class="[a-zA-Z0-9]*">#', '', $data);
                // file_put_contents($filename, $data);
                
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
                $dom->save($filename);
            }
            elseif (Str::lower($extension) == $slug) {

                $writer = IOFactory::createWriter($this->getSpreadsheetObject(), $base);
                $writer->save($filename);
                
            }
        }

        if ($writer == null) {
            throw new \InvalidArgumentException(
                "Unsupported file type for writing. Use " . implode(',', self::$instance->writers));
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
