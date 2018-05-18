<?php
namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\Style;

class CssParser
{
    private $color_params = [
        'background-color',
        'border-top-color',
        'border-right-color',
        'border-bottom-color',
        'border-left-color',
        'color'
    ];

    /**
     * border-xx-style determinam a forma como as linhas serão
     * desenhadas. As seguintes opções estão disponíveis:
     * dash-dot, dash-dot-dot, dashed, dotted, double, hair, medium,
     * medium-dash-dot, medium-dashed, slant-dash-dot, thick, thin
     * none
     * @var array
     */
    private $border_style_params = [
        'border-top-style',
        'border-right-style',
        'border-bottom-style',
        'border-left-style'
    ];

    /**
     * Interpreta os estilos e devolve-os devidamente
     * corrigidos para a planilha
     * @param  array $styles
     * @return array ou false
     */
    public static function parse($styles)
    {
        $instance = new self;
        return $instance->parseStyles($styles);
    }

    protected function parseStyles($styles)
    {
        foreach ($styles as $param => $value) {
            if(in_array($param, $this->color_params) == true) {
                $hex = $this->parseHex($value);
                $styles[$param] = new Style\Color($hex);
            } elseif(in_array($param, $this->border_style_params) == true) {
                $styles[$param] = $this->getMappedBorderStyle($value);
            } elseif ($param == 'text-align') {
                $styles[$param] = $this->getMappedHorizontalAlign($value);
            } elseif ($param == 'vertical-align') {
                $styles[$param] = $this->getMappedVerticalAlign($value);
            } elseif ($param == 'font-face') {
                $styles[$param] = $value;
            } elseif ($param == 'font-size' || $param == 'line-height') {
                $styles[$param] = preg_replace('#[^0-9]#', '', $value);
            } elseif ($param == 'font-weight') {
                $styles[$param] = ($value=='bold');
            } elseif ($param == 'font-style') {
                $styles[$param] = ($value=='italic');
            }
        }
        return $styles;
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
     * Devolve o parâmetro correto de alinhamento vertical, com base
     * nas constantes da biblioteca PHPSpreadsheet
     *
     * @return string
     */
    private function getMappedVerticalAlign($param)
    {
        switch($param) {
            case 'top':
                $align = Style\Alignment::VERTICAL_TOP;
                break;
            case 'middle':
                $align = Style\Alignment::VERTICAL_CENTER;
                break;
            case 'bottom':
                $align = Style\Alignment::VERTICAL_BOTTOM;
                break;
            default:
                $align = Style\Alignment::VERTICAL_CENTER;
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
                $align = Style\Alignment::HORIZONTAL_LEFT;
                break;
            case 'right':
                $align = Style\Alignment::HORIZONTAL_RIGHT;
                break;
            case 'center':
                $align = Style\Alignment::HORIZONTAL_CENTER;
                break;
            case 'justify':
                $align = Style\Alignment::HORIZONTAL_JUSTIFY;
                break;
            default:
                $align = Style\Alignment::HORIZONTAL_LEFT;
        }

        return $align;
    }

    /**
     * Devolve o parâmetro correto de uma borda, com base
     * nas constantes da biblioteca PHPSpreadsheet
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
}
