<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DateParser
{
    // Formatos do datetime
    const DT_ATOM = "Y-m-d\TH:i:sP" ;
    const DT_COOKIE = "l, d-M-Y H:i:s T" ;
    const DT_ISO8601 = "Y-m-d\TH:i:sO" ;
    const DT_RFC822 = "D, d M y H:i:s O" ;
    const DT_RFC850 = "l, d-M-y H:i:s T" ;
    const DT_RFC1036 = "D, d M y H:i:s O" ;
    const DT_RFC1123 = "D, d M Y H:i:s O" ;
    const DT_RFC2822 = "D, d M Y H:i:s O" ;
    const DT_RFC3339 = "Y-m-d\TH:i:sP" ;
    const DT_RSS = "D, d M Y H:i:s O" ;
    const DT_W3C = "Y-m-d\TH:i:sP" ;

    // Formatos do excel
    // @see PhpOffice\PhpSpreadsheet\Style\NumberFormat
    const EX_DASH_YYYY_MM_DD = 'yyyy-mm-dd';
    const EX_DASH_YY_MM_DD = 'yy-mm-dd';
    const EX_DASH_D_M_YY = 'd-m-yy';
    const EX_DASH_D_M = 'd-m';
    const EX_DASH_M_YY = 'm-yy';
    const EX_BAR_DD_MM_YY = 'dd/mm/yy';
    const EX_BAR_D_M_YY = 'd/m/yy';
    const EX_BAR_YY_MM_DD_AT = 'yy/mm/dd;@';
    const EX_DATE_XLSX14 = 'mm-dd-yy';
    const EX_DATE_XLSX15 = 'd-mmm-yy';
    const EX_DATE_XLSX16 = 'd-mmm';
    const EX_DATE_XLSX17 = 'mmm-yy';
    const EX_DATETIME_XLSX22 = 'm/d/yy h:mm';
    const EX_DATETIME = 'd/m/yy h:mm';
    const EX_TIME_MED_H_MM = 'h:mm AM/PM';
    const EX_TIME_MED_H_MM_SS = 'h:mm:ss AM/PM';
    const EX_TIME_H_MM = 'h:mm';
    const EX_TIME_H_MM_SS = 'h:mm:ss';
    const EX_TIME_MM_SS = 'mm:ss';
    const EX_TIME_I_S_S = 'i:s.S';
    const EX_TIME_H_MM_SS_AT = 'h:mm:ss;@';

    private static $instance = null;

    private $timestamp = null;

    public $debug = [
        'type'      => null,
        'method'    => null,
        'parser'    => null,
        'original'  => null,
        'fixed'     => null,
        'returned'  => null
    ];

    public static function getDebug()
    {
        return self::$instance->debug;
    }

    /**
     * Parseia a data especificada e devolte um timestamp ou false
     *
     * @param string $string
     */
    public static function parse($input, $format = null, $timezone = null)
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        self::$instance->debug['original'] = $input;

        // Já é um timestamp!
        if (self::$instance->isUnixTimeStamp($input) == true) {
            self::$instance->timestamp = $input;
            self::$instance->debug['type'] = 'timestamp';
            self::$instance->debug['returned'] = $input;
            $result = true;
        } else {
            $timezone = $timezone==null ? 'UTC' : $timezone;
            if ($format != null) {
                $result = self::$instance->parseFormat($input, $format, $timezone);
            } else {
                $result = self::$instance->parseAuto($input, $timezone);
            }
        }

        return $result == false ? false : self::$instance;
    }

    private function parseFormat($input, $format = null, $timezone = null)
    {
        $this->debug['method'] = 'format';

        // String não numérica
        if (is_string($input) && is_numeric($input) == false) {
            $this->debug['parser'] = 'php';
            $value = \DateTime::createFromFormat($input, $format);
            if ($value !== false) {
                $this->timestamp = $value->format('U');
                $this->debug['returned'] = $this->timestamp;
                return true;
            }
        } else {
            $this->debug['parser'] = 'excel';
            $value = ExcelDate::excelToTimestamp($input, $timezone);
            if($value != 0) {
                $this->timestamp = $value;
                $this->debug['returned'] = $this->timestamp;
                return true;
            }
        }

        return false;
    }

    private function parseAuto($input, $timezone = null)
    {
        $this->debug['method'] = 'auto';

        $value = $input;

        // String não numérica
        if (is_string($input) && is_numeric($input) == false) {
            $this->debug['parser'] = 'php';
            $input = str_replace(['/', '.'], '-', $input);
            $this->debug['fixed'] = $input;
            $value = strtotime($input);
            if ($value !== false) {
                $this->timestamp = $value;
                $this->debug['returned'] = $this->timestamp;
                return true;
            }
        }

        // Inteiro ou String numérica
        if(is_int($input) == true || is_numeric($input) == true) {
            $this->debug['parser'] = 'excel';
            $value = ExcelDate::excelToTimestamp($input, $timezone);
            if($value != 0) {
                $this->timestamp = $value;
                $this->debug['returned'] = $this->timestamp;
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o valor passado é um timestamp unix válido.
     * @param  mixed  $timestamp
     * @return boolean
     */
    public function isUnixTimeStamp($timestamp)
    {
        $check = (is_int($timestamp) OR is_float($timestamp))
		      ? $timestamp
              : (string) (int) $timestamp;

    	return  ($check === $timestamp)
                // o maior valor inteiro possivel no PHP
            	AND ( (int) $timestamp <=  PHP_INT_MAX)
            	AND ( (int) $timestamp >= ~PHP_INT_MAX)
                // Timestamps unix são contados a partir de 01-01-1970.
                AND ( (int) (date('Y', $timestamp)) > 1970);
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getExcel()
    {
        return ExcelDate::timestampToExcel($this->timestamp);
    }

    public function format($format)
    {
        $date = new \DateTime;
        $date->setTimestamp($this->timestamp);
        return $date->format($format);
    }





    private function isExcelFormat($format)
    {
        switch($format) {
            case EX_DASH_YYYY_MM_DD:
            case EX_DASH_YY_MM_DD:
            case EX_DASH_D_M_YY:
            case EX_DASH_D_M:
            case EX_DASH_M_YY:
            case EX_BAR_DD_MM_YY:
            case EX_BAR_D_M_YY:
            case EX_BAR_YY_MM_DD_AT:
            case EX_DATE_XLSX14:
            case EX_DATE_XLSX15:
            case EX_DATE_XLSX16:
            case EX_DATE_XLSX17:
            case EX_DATETIME_XLSX22:
            case EX_DATETIME:
            case EX_TIME_MED_H_MM:
            case EX_TIME_MED_H_MM_SS:
            case EX_TIME_H_MM:
            case EX_TIME_H_MM_SS:
            case EX_TIME_MM_SS:
            case EX_TIME_I_S_S:
            case EX_TIME_H_MM_SS_AT:
                return true;
        }
    }
}
