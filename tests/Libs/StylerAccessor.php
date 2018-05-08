<?php

namespace ReportCollection\Tests\Libs;

use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;

/**
 * Classe para permitir acesso aos metodos protegidos
 * dentro dos testes de unidade
 */
class StylerAccessor extends Styler
{
    public function accessResolveRange($range)
    {
        return $this->resolveRange($range);
    }

    public function accessGetColumnNumber($vowel)
    {
        return $this->getColumnNumber($vowel);
    }

    public function accessApplyBorderStyle($row, $col, $param, $value)
    {
        return $this->applyBorderStyle($row, $col, $param, $value);
    }

}
