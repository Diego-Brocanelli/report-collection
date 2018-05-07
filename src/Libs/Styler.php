<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Styler
{
    /** @var ReportCollection\Libs\Reader */
    private $reader = null;

    /** @var array */
    private $buffer = null;

    /** @var array */
    private $default_styles = [

        'background-color'    => '#f5f5f5',

        'border-top-color'    => '#555555',
        'border-right-color'  => '#555555',
        'border-bottom-color' => '#555555',
        'border-left-color'   => '#555555',

        // none,
        // dash-dot, dash-dot-dot, dashed, dotted, double, hair, medium,
        // medium-dash-dot, medium-dashed, slant-dash-dot, thick, thin
        'border-top-style'    => 'thick',
        'border-right-style'  => 'thick',
        'border-bottom-style' => 'thick',
        'border-left-style'   => 'thick',

        'line-height'    => '25',

        'color'          => '#555555',
        'font-face'      => 'Arial',
        'font-size'      => '11',
        'font-weight'    => 'normal',
        'font-style'     => 'normal',
        'vertical-align' => 'middle',
        'text-align'     => 'left',
    ];

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

    public function getDefaultStyles()
    {
        return $this->default_styles;
    }

    /**
     *  Devolve os dados estruturados para estilização.
     * @return array
     */
    public function getBuffer()
    {
        if($this->buffer == null) {
            $this->buffer = [];
            foreach ($this->reader->toArray() as $row => $children) {
                if(isset($this->buffer[$row]) == false) {
                    $this->buffer[$row] = [];
                }

                foreach ($children as $col => $value) {
                    $this->buffer[$row][$col] = [
                        'value' => $value,
                        'styles' => []
                    ];
                }
            }
        }
        return $this->buffer;
    }

    /**
     * Aplica estilos no range especificado.
     * @return array
     */
    public function setStyles($range, $styles = [])
    {
        $range = explode(':', $range);
        $row = (int) $range[0];
        if (isset($range[1]) == true) {
            $col = is_numeric($range[1])
                ? intval($range[1])
                : $this->getColumnNumber($range[1]);
        } else {
            $col = null;
        }

        $this->applyStyles($row, $col, $styles);

        return $this;
    }

    /**
     * Aplica os estilos.
     * @param [type] $styles [description]
     */
    protected function applyStyles($row, $col, $styles)
    {
        $buffer = $this->getBuffer();

        if (!isset($buffer[$row])) {
            return false;
        }

        if (!isset($buffer[$row][$col])) {
            return false;
        }

        $current_styles = $this->buffer[$row][$col]['styles'];

        foreach ($styles as $param => $value) {

            if (!isset($this->getDefaultStyles()[$param])) {
                // Apenas estilos válidos são permitidos
                throw new InvalidArgumentException("Invalid style {$param}");
            } else {

                $border_styles = [
                    'border-top-color',
                    'border-right-color',
                    'border-bottom-color',
                    'border-left-color',
                    'border-top-style',
                    'border-right-style',
                    'border-bottom-style',
                    'border-left-style',
                ];
                // As bordas são aplicadas de forma mais complexa
                if (in_array($param, $border_styles) == true) {
                    $value = $this->resolveBorderStyle($param, $value);
                }

                $current_styles[$param] = $value;
            }
        }

        $this->buffer[$row][$col]['styles'] = $current_styles;
    }

    protected function resolveBorderStyle($param, $value)
    {
        // 'border-top-color',
        // 'border-right-color',
        // 'border-bottom-color',
        // 'border-left-color',
        // 'border-top-style',
        // 'border-right-style',
        // 'border-bottom-style',
        // 'border-left-style',
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
     * Devolve os dados em forma de array.
     *
     * @return array
     */
    public function toArray()
    {
        if($this->buffer !== null) {
            return $this->buffer;
        }

        return $this->buffer;
    }

}
