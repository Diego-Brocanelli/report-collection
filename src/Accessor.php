<?php 

namespace ReportCollection;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use Illuminate\Support\Str;

class Accessor
{
    /**
     * Carrega e inclui os helpers do pacote
     * 
     * @return void
     */
    public function loadHelpers()
    {
        include('helpers.php');
    }

    public function createFromFile($filename, $force_extension = null)
    {
        return \ReportCollection::createFromFile($filename, $force_extension);
    }

    public function createFromHtmlString($string)
    {
        return \ReportCollection::createFromHtmlString($string);
    }

    public function createFromBuilder($model)
    {
        $array = [];
        //...

        return \ReportCollection::createFromArray($array);
    }
}
