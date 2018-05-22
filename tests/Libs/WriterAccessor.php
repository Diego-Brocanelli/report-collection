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
}
