<?php

namespace ReportCollection\Tests\Libs;

use ReportCollection\Libs\Reader;
use ReportCollection\Libs\Styler;
use ReportCollection\Libs\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Classe para permitir acesso aos metodos protegidos
 * dentro dos testes de unidade
 */
class ReaderAccessor extends Reader
{
    public function accessParseDataFromSpreadsheet(Spreadsheet $sheet, $extension)
    {
         return $this->accessParseDataFromSpreadsheet($sheet, $extension);
    }

    public function accessParseDataFromArray(array $data)
    {
         return $this->parseDataFromArray($data);
    }
}
