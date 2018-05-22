<?php

namespace ReportCollection\Tests\Libs;

use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;

/**
 * Classe para permitir acesso aos metodos protegidos
 * dentro dos testes de unidade
 */
class WriterAccessor extends Writer
{
    public function accessGetColumnVowel($number)
    {
         return $this->getColumnVowel($number);
    }

    public function accessCalcColumnWidth($vowel, $text)
    {
         return $this->calcColumnWidth($vowel, $text);
    }

    public function accessGetColumnWidth($vowel)
    {
        return $this->getColumnWidth($vowel);
    }

    // public function accessApplyStyles($row, $col, $styles)
    // {
    //     return $this->applyStyles($row, $col, $styles);
    // }
    //
    // public function accessApplyBorderStyle($row, $col, $param, $value)
    // {
    //     return $this->applyBorderStyle($row, $col, $param, $value);
    // }
}
