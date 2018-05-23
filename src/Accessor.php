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
     * @param object $object
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromObject(object $object)
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
    public static function createFromFile(string $filename, $force_extension = null)
    {
        return Collector::createFromFile($filename, $force_extension);
    }

    /**
     * Importa os dados a partir de um arquivo CSV.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromCsv(string $filename)
    {
        return Collector::createFromCsv($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo Gnumeric.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromGnumeric(string $filename)
    {
        return Collector::createFromGnumeric($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo HTML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromHtml(string $filename)
    {
        return Collector::createFromHtml($filename);
    }

    /**
     * Importa os dados a partir de um trecho de código html
     *
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromHtmlString(string $string)
    {
        return Collector::createFromHtmlString($string);
    }

    /**
     * Importa os dados a a partir de um arquivo ODS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromOds(string $filename)
    {
        return Collector::createFromOds($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo SLK.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromSlk(string $filename)
    {
        return Collector::createFromSlk($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XLS.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXls(string $filename)
    {
        return Collector::createFromXls($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XLSX.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXlsx(string $filename)
    {
        return Collector::createFromXlsx($filename);
    }

    /**
     * Importa os dados a a partir de um arquivo XML.
     *
     * @param string $filename Caminho completo até o arquivo
     * @return ReportCollection\Libs\Collector
     */
    public static function createFromXml(string $filename)
    {
        return Collector::createFromXml($filename);
    }
}
