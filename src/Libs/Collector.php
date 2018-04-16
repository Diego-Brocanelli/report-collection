<?php 

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use Illuminate\Support\Str;

class Collector
{
    /** @var ReportCollection\Libs\Collector */
    private static $instance = null;

    /** @var PhpOffice\PhpSpreadsheet\Spreadsheet */
    private $buffer = null;

    /** @var array */
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

    /** @var string */
    private $title = 'Report Document';

    /** @var string */
    private $header_logo = null;

    /** @var array */
    private $header_rows = [];

    /** @var array */
    private $default_styles = [
        'background-color-odd'  => '#ffffff', // ímpar
        'background-color-even' => '#f5f5f5', // par

        'border-color-inside'   => '#eeeeee',
        'border-color-outside'  => '#555555',

        // none, 
        // dash-dot, dash-dot-dot, dashed, dotted, double, hair, medium, 
        // medium-dash-dot, medium-dashed, slant-dash-dot, thick, thin
        'border-style-inside'   => 'thin',
        'border-style-outside'  => 'thick',

        'line-height'           => '25',
        
        'color'                 => '#555555',
        'font-face'             => 'Arial',
        'font-size'             => '11',
        'font-weight'           => 'normal',
        'font-style'            => 'normal',
        'vertical-align'        => 'middle',
        'text-align'            => 'left',
    ];

    /** @var array */
    private $header_styles = [
        'background-color-odd'  => '#555555',
        'background-color-even' => '#555555',
        'border-color-inside'   => '#444444',
        'border-color-outside'  => '#555555',
        'color'                 => '#ffffff',
    ];

    /** @var array */
    private $body_styles = [];

    /** @var array */
    private static $debug = [];

    /** @var int Para acessar o texto sem formatação */
    private $last_text_content = null;

    /** @var int Lazy Load */
    private $last_col = null;

    /** @var int Lazy Load*/
    private $last_row = null;

    //
    // Métodos Construtores
    //

    protected function __construct()
    {
        // Prepara os estilos padrões
        $this->header_styles = $this->normalizeStyles($this->header_styles);
        $this->body_styles   = $this->normalizeStyles($this->body_styles);
    }

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
        self::$instance = new Collector;

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

        self::$debug = ['created_with' => __METHOD__];

        return self::$instance;
    }

    /**
     * Cria uma planilha a partir de um trecho de código html
     * 
     * @param string $string
     */
    public static function createFromHtmlString($string)
    {
        self::$instance = new Collector;

        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), uniqid('report-collection'));
        file_put_contents($temp_file, $string);

        // Carrega o arquivo na planilha
        self::$instance->createFromFile($temp_file, 'html');
        unlink($temp_file);

        self::$debug = ['created_with' => __METHOD__];

        return self::$instance;
    }

    /**
     * Cria uma planilha a partir de um array
     * 
     * @param array $array
     */
    public static function createFromArray(array $array)
    {
        self::$instance = new Collector;

        self::$instance->buffer = null;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $array,  // os dados a adicionar
                NULL     // itens com este valor não serão setados
            );

        self::$instance->buffer = $spreadsheet;

        self::$debug = ['created_with' => __METHOD__];

        return self::$instance;
    }

    /**
     * Cria uma planilha a partir de um objeto.
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
        self::$instance = new Collector;

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

        self::$debug = ['created_with' => __METHOD__];

        return self::$instance;
    }


    //
    // Informações da planilha
    //

    /**
     * @return PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function getSpreadsheetObject()
    {
        return $this->buffer;
    }

    /**
     * @return PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getActiveSheet()
    {
        return $this->getSpreadsheetObject()->getActiveSheet();
    }

    /**
     * Devolve a última linha da planilha.
     * 
     * @return int
     */
    public function getLastRow() 
    {
        if($this->last_row == null) {
            $this->last_row = (int) $this->getActiveSheet()->getHighestRow();
        }

        return $this->last_row;
    }

    /**
     * Devolve a última coluna da planilha.
     * 
     * @return int
     */
    public function getLastColumn() 
    {
        if($this->last_col == null) {
            $col = $this->getActiveSheet()->getHighestColumn();
            $this->last_col = $this->getColumnNumber($col);
        }

        return $this->last_col;
    }

    //
    // Estilos
    //

    /**
     * Seta estilos para personalizar o cabeçalho da planilha.
     * 
     * @return array
     */
    public function setHeaderStyles(array $styles)
    {
        $this->header_styles = $this->normalizeStyles($styles, 'header');
        return $this;
    }

    /**
     * Seta estilos para personalizar o corpo da planilha.
     * 
     * @return array
     */
    public function setBodyStyles(array $styles)
    {
        $this->body_styles = $this->normalizeStyles($styles, 'body');
        return $this;
    }

    /**
     * @return array
     */
    public function getStyles($target = 'default')
    {
        $target = $target . "_styles";
        return $this->{$target};
    }

    //
    // Header
    //

    public function setHeaderLogo($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException("File is not exists.");
        }

        $this->header_logo = $filename;
    }

    public function addHeaderRow($content, array $styles = [], $colspan = 'auto')
    {
        $this->header_rows[] = [
            'content' => $content,
            'colspan' => $colspan,
            'styles'  => $styles
        ];
    }

    private function getHeaderNumRows()
    {
        return count($this->header_rows);
    }


    //
    // Formatação
    //

    /**
     * Prepara e configura a planilha.
     */
    private function setupSpreadsheet()
    {
        $this->applyStyles('default');
    }

    /**
     * Prepara e configura o corpo da planilha.
     */
    private function setupBody()
    {
        $ended_row = $this->getLastRow();
        $ended_col = $this->getLastColumn();

        // Aplica os estilos a cada célula independente
        for ($row=1; $row<=$ended_row; $row++) {

            for ($col=1; $col<=$ended_col; $col++) {
                $this->applyStyles('body', $row, $col);
            }
        }
    }    

    /**
     * Prepara e configura o cabeçalho da planilha.
     */
    private function setupHeader()
    {
        // Não existem linhas de cabeçalho
        if ($this->getHeaderNumRows() == 0) {
            return false;
        }

        foreach ($this->header_rows as $index => $row) {

            $line = $index+1;

            // Insere a nova linha
            $this->getActiveSheet()->insertNewRowBefore($line, 1);

            // A linha possui estilos personalizados?
            $styles = count($row['styles'])>0 ? $row['styles'] : [];
            $styles = $this->normalizeStyles($styles, 'header');

            // Mescla as colunas para criar as linhas de cabeçalho            
            if ($row['colspan'] == 'auto') {
                $row['colspan'] = $this->getLastColumn();
            }
            $colspan = $this->getColumnVowel($row['colspan']);
            $this->getActiveSheet()->mergeCells("A{$line}:{$colspan}{$line}");

            // Adiciona o conteudo formatado na linha
            $content = $this->parseHtmlText($row['content'], $styles);
            $this->getActiveSheet()->getCell("A{$line}")->setValue($content);

            $this->debugStyle('header', $line, 1, 'text-content', $this->last_text_content);
            $this->debugStyle('header', $line, 1, 'row-styles-setted', $row['styles']);

            // Aplica os estilos
            $this->applyStyles('header', $line, 1, $styles);
        }
    }

    private function getTargetObject($target, $row = null, $col = null)
    {
        if ($target == 'default') {

            $object = $this->getSpreadsheetObject()->getDefaultStyle();

        } else {

            // body|header

            $row = ($row == null) ? 1 : $row;
            $col = ($col == null) ? $this->getLastColumn() : $col;
            $range = $this->getColumnVowel($col).$row;

            $object = $this->getActiveSheet()->getStyle($range);
        }

        return $object;
    }

    private function applyStyles($target, $row = null, $col = null, array $styles = null)
    {
        // Quais estilos aplicar
        if ($styles==null) {
            $styles = $this->getStyles($target);
        } else {
            $styles = $this->normalizeStyles($styles);
            $this->debugStyle($target, $row, $col, 'row-styles-normal', $styles);
        }

        // Alvo da estilização
        $object = $this->getTargetObject($target, $row, $col);

        foreach ($styles as $param => $value) {

            // Não será aplicado
            if ($value == 'none') {
                continue;
            }

            switch($param) {

                case 'color':
                    $color = $this->createColor($value);
                    $object->getFont()->setColor($color);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'font-face':
                    $object->getFont()->setName($value);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'font-size':
                    $object->getFont()->setSize($value);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'font-weight':
                    $value = $value=='bold';
                    $object->getFont()->setBold($value);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'font-style':
                    $value = $value=='italic';
                    $object->getFont()->setItalic($value);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'line-height':
                    $this->setLineHeight($value, $target, $row, $col);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'vertical-align':
                    $align = $this->getMappedVerticalAlign($value);
                    $object->getAlignment()->setVertical($align);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'text-align':
                    $align = $this->getMappedHorizontalAlign($value);                    
                    $object->getAlignment()->setHorizontal($align);
                    $this->debugStyle($target, $row, $col, $param, $value);
                    break;

                case 'background-color-odd':
                // case 'background-color-even': <- even não é necessário pois o parametro é dinamico
                    $this->setBackgroundStyle($styles, $target, $row, $col);
                    break;

                case 'border-color-inside':
                // case 'border-color-outside': não são necessários pois o parametro é dinamico
                // case 'border-style-inside':
                // case 'border-style-outside':
                    $this->setBorderStyle($styles, $target, $row, $col);
                    break;
            }
        }
    }

    private function setBackgroundStyle(array $styles, $target, $row, $col)
    {
        // Para controlar odd e even
        $row_control = $row-1;

        $param = ($row_control%2 == 0)
            ? 'background-color-odd' 
            : 'background-color-even';

        if (!isset($styles[$param])) {
            throw new OutOfBoundsException("The '{$param}' parameter is not present in the style list");
        }

        // Se valor for 'none', não aplica
        $value = $styles[$param];
        if ($value == 'none') {
            return false;
        }

        // Se não for aplicado no documento todo
        // a a coluna extrapolar a última, não aplica
        if ($target !== 'default' && $col > $this->getLastColumn()) {
            return false;
        }

        $color = $this->createColor($value);
        $this->getTargetObject($target, $row, $col)
             ->getFill()
             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
             ->setStartColor($color);

        if ($target == 'default') {
            $this->debugStyle($target, $row, $col, 'background', $styles['background-color-odd']);
        } else {
            $this->debugStyle($target, $row, $col, 'background', $value);
        }
    }

    private function setBorderStyle(array $styles, $target, $row, $col)
    {
        $requireds = [
            'border-color-inside',
            'border-color-outside',
            'border-style-inside',
            'border-style-outside'
        ];
        foreach ($requireds as $param) {
            if (!isset($styles[$param])) {
                throw new OutOfBoundsException("The '{$param}' parameter is not present in the style list");
            }
        }

        // Bordas não são setadas no documento todo
        if ($target == 'default') {
            return false;
        }

        if ($row == null) {
            throw new InvalidArgumentException("The \$row argument can not be null for the target other than 'default'");
        }
        if ($col == null) {
            throw new InvalidArgumentException("The \$col argument can not be null for the target other than 'default'");
        }        

        // Bordas internas
        // --------------------------------------------------
        
        if ($styles['border-style-inside'] != 'none') {

            $inside_style = $this->getMappedBorderStyle($styles['border-style-inside']);
            $inside_color = $this->createColor($styles['border-color-inside']);

            if ($row != 1) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getTop()
                     ->setBorderStyle($inside_style)
                     ->setColor($inside_color);
            }

            if ($col != 1) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getLeft()
                     ->setBorderStyle($inside_style)
                     ->setColor($inside_color);
            }

        }

        if ($styles['border-style-outside'] != 'none') {

            $outside_style = $this->getMappedBorderStyle($styles['border-style-outside']);
            $outside_color = $this->createColor($styles['border-color-outside']);

            if ($row == 1) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getTop()
                     ->setBorderStyle($outside_style)
                     ->setColor($outside_color);
            }

            if ($row == $this->getLastRow()) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getBottom()
                     ->setBorderStyle($outside_style)
                     ->setColor($outside_color);
            }

            if ($col == 1) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getLeft()
                     ->setBorderStyle($outside_style)
                     ->setColor($outside_color);
            }

            if ($col == $this->getLastColumn()) {
                $this->getTargetObject($target, $row, $col)
                     ->getBorders()->getRight()
                     ->setBorderStyle($outside_style)
                     ->setColor($outside_color);
            }

        }
    }

    private function setLineHeight($height_value, $target, $row = null, $col = null)
    {
        $height_value = intval($height_value);

        if ($target != 'default') {

            if ($row == null) {
                throw new InvalidArgumentException("The \$row argument can not be null for the target other than 'default'");
            }
            if ($col == null) {
                throw new InvalidArgumentException("The \$col argument can not be null for the target other than 'default'");
            }

            // Aplica a altura na linha especificada
            // Passando pela primeira coluna, aplica na linha toda
            if ($col == 1) {
                $this->getActiveSheet()->getRowDimension($row)->setRowHeight($height_value);    
            }

        } else {

            // Aplica a altura no documento todo
            $this->getActiveSheet()->getDefaultRowDimension()->setRowHeight($height_value);
        }
    }

    // private function applyStylesBackgrounds($target, $row = null, $col = null, array $styles = null)
    // {
    //     $styles = $styles==null
    //         ? $this->getStyles($target)
    //         : $styles;

    //     if ($target == 'default') {
    //         $range     = null;
    //         $type_cell = 'default';
    //         $type_row  = 'default';
    //         $object    = $this->getSpreadsheetObject()->getDefaultStyle();
    //     }
    //     else {

    //         if($row == null || $col == null) {
    //             throw new InvalidArgumentException("Arguments row and col can not be null");
    //         }

    //         $range     = $this->getColumnVowel($col).$row;
    //         $type_cell = 'cell';
    //         $type_row  = 'row';
    //         $object    = $this->getActiveSheet()->getStyle($range);
    //     }

    //     // Bordas
    //     $inside_color_value = 'FF' . trim(strtoupper($styles['border-color-inside']), '#');
    //     $inside_color = new \PhpOffice\PhpSpreadsheet\Style\Color($inside_color_value);
    //     $inside_style = $this->getMappedBorderStyle($styles['border-style-inside']);

    //     $outside_color_value = 'FF' . trim(strtoupper($styles['border-color-outside']), '#');
    //     $outside_color = new \PhpOffice\PhpSpreadsheet\Style\Color($outside_color_value);
    //     $outside_style = $this->getMappedBorderStyle($styles['border-style-outside']);

    //     if ($target == 'body') {
    //         $start_row = 1;
    //         $ended_row = $this->getLastRow();
    //         $ended_col = $this->getLastColumn();
    //     }
    //     elseif($target == 'header') {
    //         $start_row = 1;
    //         $ended_row = $this->getHeaderNumRows();
    //         $ended_col = $this->getColumnVowel(1);
    //     }

    //     foreach ($styles as $param => $value) {

    //         $background_param = ($this->row_control%2 == 0)
    //             ? 'background-color-odd' : 'background-color-even';

    //         if ($value == 'none') {
    //             continue;
    //         }

    //         switch($param) {

    //             case 'border-style-inside':

    //                 if ($target !== 'default') {

    //                     if ($row > $start_row && $col <= $this->getColumnNumber($ended_col)) {
    //                         $object->getBorders()->getTop()
    //                             ->setBorderStyle($inside_style)
    //                             ->setColor($inside_color);
    //                     }

    //                     if ($col > 1 && $col <= $this->getColumnNumber($ended_col)) {
    //                         $object->getBorders()->getLeft()
    //                             ->setBorderStyle($inside_style)
    //                             ->setColor($inside_color);
    //                     }
    //                 }
    //                 break;

    //             case 'border-style-outside':

    //                 if ($target !== 'default') {

    //                     if ($row == $start_row && $col <= $this->getColumnNumber($ended_col)) {
    //                         $object->getBorders()->getTop()
    //                             ->setBorderStyle($outside_style)
    //                             ->setColor($outside_color);
    //                     }

    //                     if ($row == $ended_row && $col <= $this->getColumnNumber($ended_col)) {
    //                         $object->getBorders()->getBottom()
    //                             ->setBorderStyle($outside_style)
    //                             ->setColor($outside_color); 
    //                     }

    //                     if ($col == 1) {
    //                         $object->getBorders()->getLeft()
    //                             ->setBorderStyle($outside_style)
    //                             ->setColor($outside_color);
    //                     }

    //                     if ($col == $this->getColumnNumber($ended_col)) {
    //                         $object->getBorders()->getRight()
    //                             ->setBorderStyle($outside_style)
    //                             ->setColor($outside_color);
    //                     }
    //                 }
    //                 break;

    //             case 'background-color-odd':
    //             // case 'background-color-even': <- even não é necessário pois o parametro é dinamico

    //                 if ($styles[$background_param] == 'none') {
    //                     continue;
    //                 }

    //                 if ($target !== 'default' && $col > $this->getColumnNumber($ended_col)) {
    //                     continue;
    //                 }
                    
    //                 $value = 'FF' . trim(strtoupper($styles[$background_param]), '#');
    //                 $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
    //                 $object->getFill()
    //                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //                      ->setStartColor($color);
    //                 $this->debugStyle($range, 'background', str_replace('background-color-', '', $background_param).":".$value, $type_cell);
    //                 break;

    //             case 'color':
    //                 $value = 'FF' . trim(strtoupper($value), '#');
    //                 $color = new \PhpOffice\PhpSpreadsheet\Style\Color($value);
    //                 $object->getFont()->setColor($color);
    //                 $this->debugStyle($range, $param, $value, $type_cell);
    //                 break;

    //             case 'font-face':
    //                 $object->getFont()->setName($value);
    //                 $this->debugStyle($range, $param, $value, $type_cell);
    //                 break;

    //             case 'font-size':
    //                 $object->getFont()->setSize($value);
    //                 $this->debugStyle($range, $param, $value, $type_cell);
    //                 break;

    //             case 'font-weight':
    //                 $value = $value=='bold';
    //                 $object->getFont()->setBold($value);
    //                 $this->debugStyle($range, $param, "setBold(".var_export($value, true).")", $type_cell);
    //                 break;

    //             case 'font-style':
    //                 $value = $value=='italic';
    //                 $object->getFont()->setItalic($value=='italic');
    //                 $this->debugStyle($range, $param, "setItalic(".var_export($value, true).")", $type_cell);
    //                 break;

    //             case 'line-height':
    //                 $value = intval($value);
    //                 if ($target == 'default') {
    //                     $this->getActiveSheet()->getDefaultRowDimension()->setRowHeight($value);
    //                 }
    //                 elseif ($col = 1) {
    //                     // Para aplicar apenas uma vez por linha
    //                     $this->getActiveSheet()->getRowDimension($row)->setRowHeight($value);
    //                 }
    //                 $this->debugStyle($row, $param, $value, $type_row);
    //                 break;

    //             // case 'border-style':
    //             //     $object->getBorders()->getLeft($value=='italic');
    //             //     break;

    //             case 'vertical-align':
    //                 switch($value) {
    //                     case 'top':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP;
    //                         break;
    //                     case 'middle':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
    //                         break;
    //                     case 'bottom':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM;
    //                         break;
    //                     default:
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
    //                 }
    //                 $object->getAlignment()->setVertical($align);
    //                 $this->debugStyle($range, $param, $align, $type_cell);
    //                 break;

    //             case 'text-align':
    //                 switch($value) {
    //                     case 'left':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
    //                         break;
    //                     case 'right':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
    //                         break;
    //                     case 'center':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
    //                         break;
    //                     case 'justify':
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY;
    //                         break;
    //                     default:
    //                         $align = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
    //                 }
    //                 $object->getAlignment()->setHorizontal($align);
    //                 $this->debugStyle($range, $param, $align, $type_cell);
    //                 break;
    //         }
    //     }
    // }

    /**
     * Interpreta o conteúdo de uma string e aplica os estilos 
     * de acordo com as tags informadas
     * 
     * @return \PhpOffice\PhpSpreadsheet\RichText\RichText
     */
    private function parseHtmlText($string, array $styles = null)
    {
        $styles = $styles==null ? [] : $styles;
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

        // Armazena os pedaços o texto sem as tags
        // para o debugger capturar
        $this->last_text_content = implode('', $strips);

        $text = new \PhpOffice\PhpSpreadsheet\RichText\RichText();

        $color = $this->createColor($styles['color']);

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

    //
    // Exportação
    //

    /**
     * Devolve os dados da planilha em forma de array.
     * 
     * @return array
     */
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
     * Devolve os dados da planilha em forma de xml.
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
        $this->setupSpreadsheet();

        $this->setupBody();

        $this->setupHeader();

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

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
                "Unsupported file type for writing. Use " . implode(',', self::$instance->writers));
        }

        return $this;
    }

    /**
     * Força o download da planilha em um formato especifico.
     * As extensões suportadas são:
     * csv, html, ods, pdf, xls e xlsx
     * 
     * @param string $filename Nome do arquivo com extensão
     */
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

    //
    // Helpers
    //

    public function getDebugInfo()
    {
        return self::$debug;
    }

    /**
     * Armazena informações de debug
     */

    protected function debug($param, $value)
    {
        self::$debug[$param] = $value;
    }

    protected function debugStyle($target, $row, $col, $param, $value)
    {
        // Armazena as informações para os testes de unidade
        if (!isset(self::$debug['styles'])) {
            self::$debug['styles'] = [];
        }

        if (!isset(self::$debug['styles'][$target])) {
            self::$debug['styles'][$target] = [];
        }

        if ($target != 'default') {
            $range = $this->getColumnVowel($col).$row;
            if (!isset(self::$debug['styles'][$target][$range])) {
                self::$debug['styles'][$target][$range] = [];
            }

            self::$debug['styles'][$target][$range][$param] = $value;
        }
        else {
            self::$debug['styles'][$target][$param] = $value;
        }
    }

    /**
     * Devolve o parâmetro correto de alinhamento vertical, com base 
     * nas constantes da biblioteca PHPSpreadsheet
     * 
     * @return string
     */
    private function getMappedVerticalAlign($param)
    {
        switch($param) {
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

        return $align;
    }

    /**
     * Devolve o parâmetro correto de alinhamento vertical, com base 
     * nas constantes da biblioteca PHPSpreadsheet
     * 
     * @return string
     */
    private function getMappedHorizontalAlign($param)
    {
        switch($param) {
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

        return $align;
    }

    /**
     * Devolve o parâmetro correto de uma borda, com base 
     * nas constantes da biblioteca PHPSpreadsheet
     * 
     * @return string
     */
    private function getMappedBorderStyle($param)
    {
        $map = [
            'none'                => Style\Border::BORDER_NONE, 
            'dash-dot'            => Style\Border::BORDER_DASHDOT, 
            'dash-dot-dot'        => Style\Border::BORDER_DASHDOTDOT, 
            'dashed'              => Style\Border::BORDER_DASHED, 
            'dotted'              => Style\Border::BORDER_DOTTED, 
            'double'              => Style\Border::BORDER_DOUBLE, 
            'hair'                => Style\Border::BORDER_HAIR, 
            'medium'              => Style\Border::BORDER_MEDIUM, 
            'medium-dash-dot'     => Style\Border::BORDER_MEDIUMDASHDOT, 
            'medium-dash-dot-dot' => Style\Border::BORDER_MEDIUMDASHDOTDOT, 
            'medium-dashed'       => Style\Border::BORDER_MEDIUMDASHED, 
            'slant-dash-dot'      => Style\Border::BORDER_SLANTDASHDOT, 
            'thick'               => Style\Border::BORDER_THICK, 
            'thin'                => Style\Border::BORDER_THIN,
        ];

        return isset($map[$param]) 
            ? $map[$param] 
            : Style\Border::BORDER_NONE;
    }

    protected function createColor($hex)
    {
        $value = $this->parseHex($hex);
        return new \PhpOffice\PhpSpreadsheet\Style\Color($value);
    }

    protected function parseHex($hex)
    {
        $hex = trim(strtoupper($hex), '#');
        if (strlen($hex) == 8) {

            $fixed = $hex;

        } elseif (strlen($hex) == 6) {

            $fixed = 'FF' . $hex;

        } elseif(strlen($hex) >= 3) {

            $fixed = 'FF' . $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return $fixed;
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

    /**
     * Converte uma coluna de vogais para um valor numérico.
     * 
     * @param string $vowel
     * @return int
     */
    protected function getColumnNumber($vowel)
    {
        if (is_numeric($vowel)) {
            return (int) $vowel;
        }

        // alfabeto
        $map = range('A', 'Z');
        $map = array_flip($map);
        $vowels = count($map);

        if (strlen($vowel) == 1) {

            return isset($map[$vowel]) ? $map[$vowel]+1 : 1;

        } else {
            
            $iterations = isset($map[$vowel[0]]) ? $map[$vowel[0]]+1 : 1;
            $number = isset($map[$vowel[1]]) ? $map[$vowel[1]]+1 : 1;
            return $number + $vowels*$iterations;
        }
    }

    /**
     * Normaliza os estilos especificados, adicionando os 
     * estilos padrões quando estes não estiverem presentes.
     * 
     * @param array $styles
     * @param string $based_on header|body|default
     * @return array
     */
    public function normalizeStyles(array $styles, $based_on = 'default')
    {
        $clean_styles = [];

        switch($based_on) {
            case 'header':
                $base_styles = $this->header_styles;
                break;
            case 'body':
                $base_styles = $this->body_styles;
                break;
            case 'default':
                $base_styles = $this->default_styles;
                break;
        }
        
        foreach($base_styles as $attr => $value) {

            $clean_styles[$attr] = isset($styles[$attr])
                ? $styles[$attr] 
                : $value;
        }

        // Sobrescreve os backgrounds
        if (isset($styles['background-color'])) {
            $clean_styles['background-color-odd']  = $styles['background-color'];
            $clean_styles['background-color-even'] = $styles['background-color'];
        }

        return $clean_styles;
    }
}
