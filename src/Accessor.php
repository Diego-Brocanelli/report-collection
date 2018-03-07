<?php 

namespace ReportCollection;

use ReportCollection\Libs\Collector;

class Accessor
{

    public function createFromFile($filename, $force_extension = null)
    {
        return Collector::createFromFile($filename, $force_extension);
    }

    public function createFromHtmlString($string)
    {
        return Collector::createFromHtmlString($string);
    }

    public function createFromArray($array)
    {
        return Collector::createFromArray($array);
    }

    public function createFromObject($object)
    {
        return Collector::createFromObject($object);
    }
}
