<?php

namespace ReportCollection;

use ReportCollection\Libs\Collector;

class Accessor
{
    /**
     * Importa os dados a partir de um array
     *
     * @param array $array
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromArray(array $array)
    {
        return Collector::createFromArray($array);
    }

    /**
     * Importa os dados a partir de um objeto.
     * O objeto deve:
     *
     * Implementar o método toArray()
     * ou
     * Ser iterável
     * ou
     * Ser passível de conversão para array (via atributos)
     *
     * @param mixed $object
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromObject($object)
    {
        return Collector::createFromObject($object);
    }

    /**
     * Importa os dados a a partir de um arquivo.
     * As extensões suportadas são:
     * csv, gnumeric, htm, html, ods, slk, xls, xlsx e xml
     *
     * @param string $filename Arquivo e caminho completo
     * @param string force_extension para arquivos sem extensão
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromFile($filename, $force_extension = null)
    {
        return Collector::createFromFile($filename, $force_extension);
    }

    /**
     * Importa os dados a partir de um arquivo CSV.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromCsv($filename)
    {
        return Collector::createFromCsv($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo Gnumeric.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromGnumeric($filename)
    {
        return Collector::createFromGnumeric($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo HTML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromHtml($filename)
    {
        return Collector::createFromHtml($filename);
    }

    /**
     * Importa os dados a partir de um trecho de código html
     *
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromHtmlString($string)
    {
        return Collector::createFromHtmlString($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo ODS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromOds($filename)
    {
        return Collector::createFromOds($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo SLK.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromSlk($filename)
    {
        return Collector::createFromSlk($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XLS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXls($filename)
    {
        return Collector::createFromXls($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XLSX.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXlsx($filename)
    {
        return Collector::createFromXlsx($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXml($filename)
    {
        return Collector::createFromXml($filename);
    }
}
