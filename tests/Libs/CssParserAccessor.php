<?php

namespace ReportCollection\Tests\Libs;

use ReportCollection\Libs\CssParser;

/**
 * Classe para permitir acesso aos metodos protegidos
 * dentro dos testes de unidade
 */
class CssParserAccessor extends CssParser
{
    public static function accessParseHex($hex)
    {
        $instance = new self();
        return $instance->parseHex($hex);
    }
}
