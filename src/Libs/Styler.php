<?php 

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;

class Styler
{
    /** @var ReportCollection\Libs\Reader */
    private $reader = null;
    
    /** @var array */
    private $buffer = null;

    /**
     * Importa os dados a partir do Reader
     * 
     * @param array $array
     */
    public static function createFromReader(Reader $reader)
    {
        $instance = new self;
        $instance->reader = $reader;

        return $instance;
    }

    /**
     * Devolve os dados em forma de array.
     * 
     * @return array
     */
    public function toArray()
    {
        if($this->buffer !== null) {
            return $this->buffer;
        }

        return $this->buffer;
    }

}
