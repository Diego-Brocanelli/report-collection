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
        'background-fill'     => 'solid',
        'border-top-color'    => '#555555',
        'border-right-color'  => '#555555',
        'border-bottom-color' => '#555555',
        'border-left-color'   => '#555555',
        // border-xx-style determinam a forma como as linhas serão
        // desenhadas. As seguintes opções estão disponíveis:
        // dash-dot, dash-dot-dot, dashed, dotted, double, hair, medium,
        // medium-dash-dot, medium-dashed, slant-dash-dot, thick, thin
        // none
        'border-top-style'    => 'thick',
        'border-right-style'  => 'thick',
        'border-bottom-style' => 'thick',
        'border-left-style'   => 'thick',
        'color'          => '#555555',
        'font-face'      => 'Arial',
        'font-size'      => '11',
        'font-weight'    => 'normal',
        'font-style'     => 'normal',
        'line-height'    => '25',
        'text-align'     => 'left',
        'vertical-align' => 'middle',
    ];

    /**
     * Importa os dados a partir do Reader
     *
     * @param array $array
     * @return ReportCollection\Libs\Styler
     */
    public static function createFromReader(Reader $reader) : Styler
    {
        $classname = \get_called_class(); // para permitir abstração
        $instance = new $classname;
        $instance->reader = $reader;

        return $instance;
    }

    /**
     * Devolve a lista de estilos padrões
     *
     * @return array
     */
    public function getDefaultStyles() : array
    {
        return $this->default_styles;
    }

    /**
     *  Devolve o reader usado para a estilização dos dados.
     *
     * @return array
     */
    public function getReader() : Reader
    {
        return $this->reader;
    }

    /**
     *  Devolve os dados estruturados para estilização.
     *
     * @return array
     */
    public function getBuffer() : array
    {
        if($this->buffer !== null) {
            return $this->buffer;
        }

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

        return $this->buffer;
    }

    /**
     *  Reseta os dados do buffer, voltando-os para o estado inicial
     *  recebido do Reader.
     * @return ReportCollection\Libs\Styler
     */
    public function resetBuffer() : Styler
    {
        $this->buffer = null;
        return $this;
    }

    /**
     * Aplica estilos com base nos indices do Excel.
     *
     * @return bool
     */
    public function setStyles($range, array $styles = []) : bool
    {
        $range = $this->resolveRange($range);

        if ($range['row'] !== null && $range['col'] !== null) {
            // Coluna e linha especificados: AC22
            return $this->applyStyles($range['row'], $range['col'], $styles);
        } else {
            $buffer = $this->getBuffer();
            $apply_result = true;
            if ($range['row'] !== null) {
                // Apenas linha especificada: 22
                foreach ($buffer[0] as $col => $data) {
                    $result = $this->applyStyles($range['row'], $col, $styles);
                    $apply_result = $result == false ? false : $apply_result;
                }
            } else {
                // Apenas Coluna especificada: AC
                foreach ($buffer as $row => $cols) {
                    $result = $this->applyStyles($row, $range['col'], $styles);
                    $apply_result = $result == false ? false : $apply_result;
                }
            }
            return $apply_result;
        }
    }

    /**
     * Devolve os dados em forma de array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->getBuffer();
    }

    /**
     * Resolve o range especificado no formato do excel, devolvendo
     * os índices correspondentes aos dados do buffer.
     * As colunas devem ser vogais e as linhas numeros começando a partir de 1.
     * Veja os formatos:
     * - coluna + linha (A23)
     * - apenas linha (23)
     * - apenas coluna (A)
     *
     * @param  string $range
     * @return array
     */
    protected function resolveRange($range) : array
    {
        $row = $col = null;

        if (is_numeric($range)) {
            $row = (int) $range;
            $row = $row - 1;
        } else {
            $matches = [];
            if (preg_match_all('/([0-9]+|[a-zA-Z]+)/', $range, $matches) > 0) {

                // Há vogais seguidas de números
                if (isset($matches[0][1]) == true) {
                    $row = (int) $matches[0][1];
                    $row = $row - 1;
                } else {
                    // Não há numeros, apenas vogais
                    // $row será null
                }

                $col = is_numeric($matches[0][0])
                    ? intval($matches[0][0])
                    : $this->getColumnNumber($matches[0][0]);
                $col = $col - 1;
            }
        }

        return [ 'row' => $row, 'col' => $col];
    }

    /**
     * Aplica os estilos com base nos indices PHP.
     *
     * @param  int $row
     * @param  int $col
     * @param  array $styles
     * @return bool
     */
    protected function applyStyles($row, $col, array $styles) : bool
    {
        $buffer = $this->getBuffer();

        if (!isset($buffer[$row])) {
            return false;
        }

        if (!isset($buffer[$row][$col])) {
            return false;
        }

        $current_styles = $this->buffer[$row][$col]['styles'];

        $results = [];
        foreach ($styles as $param => $value) {

            if (!isset($this->getDefaultStyles()[$param])) {
                // Apenas estilos válidos são permitidos
                throw new \InvalidArgumentException("Invalid style {$param}");
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
                    $results[$param] = $this->applyBorderStyle($row, $col, $param, $value);
                }

                if ($value == 'none' && isset($current_styles[$param])) {
                    unset($current_styles[$param]);
                }

                if ($value != 'none') {
                    $current_styles[$param]    = $value;
                }
            }
        }

        $this->buffer[$row][$col]['styles'] = $current_styles;

        $results = array_unique($results);
        return (count($results) == 0 || current($results) === true) ? true : false;
    }

    /**
     * Aplica os estilos para bordas com base nos indices PHP.
     *
     * @param  int $row
     * @param  int $col
     * @param  string $param
     * @param  string $value
     * @return bool
     */
    protected function applyBorderStyle($row, $col, string $param, $value) : bool
    {
        $buffer = $this->getBuffer();

        if (!isset($buffer[$row])) {
            return false;
        }

        if (!isset($buffer[$row][$col])) {
            return false;
        }

        // Os estilos de borda são aplicados apenas  no topo e na esquerda
        // Isso diminui a carga na estilização da planilha e corrige possiveis
        // bugs no objeto Spreadsheet

        $names = explode('-', $param);
        $direction = $names[1]; // top, right, bottom, left

        switch($direction) {
            case 'top':
                $this->applyBorderTop($row, $col, $param, $value);
                break;

            case 'left':
                $this->applyBorderLeft($row, $col, $param, $value);
                break;

            case 'right':
                $this->applyBorderRight($row, $col, $param, $value);
                break;

            case 'bottom':
                $this->applyBorderBottom($row, $col, $param, $value);
                break;
        }

        return true;
    }

    protected function applyBorderTop($row, $col, string $param, $value)
    {
        if ($value == 'none' && isset($this->buffer[$row][$col]['styles'][$param])) {
            unset($this->buffer[$row][$col]['styles'][$param]);
        }

        if ($value != 'none') {
            // Aplica na linha atual
            $this->buffer[$row][$col]['styles'][$param] = $value;
        }
    }

    protected function applyBorderLeft($row, $col, string $param, $value)
    {
        if ($value == 'none' && isset($this->buffer[$row][$col]['styles'][$param])) {
            unset($this->buffer[$row][$col]['styles'][$param]);
        }

        if ($value != 'none') {
            // Aplica na coluna atual
            $this->buffer[$row][$col]['styles'][$param] = $value;
        }
    }

    protected function applyBorderRight($row, $col, string $param, $value)
    {
        $names = explode('-', $param);
        //$direction = $names[1]; // top, right, bottom, left
        $sufix     = $names[2]; // color, style

        if (($col+1) == count($this->buffer[$row])) {
            // se for a última coluna, aplica explicitamente
        } else {
            // aplica no left da próxima coluna
            $param = "border-left-{$sufix}";
            $col = $col+1;
        }

        if ($value == 'none' && isset($this->buffer[$row][$col]['styles'][$param])) {
            unset($this->buffer[$row][$col]['styles'][$param]);
        }

        if ($value != 'none') {
            $this->buffer[$row][$col]['styles'][$param] = $value;
        }
    }

    protected function applyBorderBottom($row, $col, string $param, $value)
    {
        $names = explode('-', $param);
        //$direction = $names[1]; // top, right, bottom, left
        $sufix     = $names[2]; // color, style

        if (($row+1) == count($this->buffer)) {
            // se for a última linha, aplica explicitamente
        } else {
            // aplica no top da próxima linha
            $param = "border-top-{$sufix}";
            $row = $row+1;
        }

        if ($value == 'none' && isset($this->buffer[$row][$col]['styles'][$param])) {
            unset($this->buffer[$row][$col]['styles'][$param]);
        }

        if ($value != 'none') {
            $this->buffer[$row][$col]['styles'][$param] = $value;
        }
    }

    /**
     * Converte uma coluna de vogais para um valor numérico.
     *
     * @param string $vowel
     * @return int
     */
    protected function getColumnNumber(string $vowel)
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
}
