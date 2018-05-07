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
    public static function createFromReader(Reader $reader)
    {
        $instance = new self;
        $instance->reader = $reader;

        return $instance;
    }

    public function accessResolveRange($range)
    {
        return $this->resolveRange($range);
    }

    public function accessGetColumnNumber($vowel)
    {
        return $this->getColumnNumber($vowel);
    }

    public function accessResolveBorderStyle($param, $value)
    {
        return $this->resolveBorderStyle($param, $value);
    }

}
