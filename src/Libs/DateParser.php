<?php

namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DateParser
{
    /** @var ReportCollection\Libs\DateParser */
    private static $instance = null;

    /** @var int */
    private $timestamp = null;

    /** @var array */
    public $debug = null;

    /**
     * Retorna informações de debug para testes de unidade.
     * São informadas através das seguintes chaves:
     * type, method, parser, original, fixed, returned, error
     * @return array
     */
    public static function getDebug()
    {
        return self::$instance->debug;
    }

    /**
     * Transforma um string válida em timestamp,
     * ou false em caso de falha.
     * Funciona como a função strtotime, só que usando timezones.
     * @see strtotime
     * @param  string $string
     * @param  string $timezone
     * @return int ou false
     */
    public function toTime($input, $timezone = 'UTC')
    {
        try {
            $datetime = new \DateTime($input, new \DateTimeZone($timezone));
            if ($datetime !== false) {
                return $datetime->format('U');
            }
        } catch (\Exception $e) {
            $this->debug['error'] = $e->getMessage();
        }

        return false;
    }

    /**
     * Interpreta a data especificada e devolve um objeto DateParse
     * ou false em caso de falha
     * @param  mixed $input
     * @param  string $format
     * @param  string $timezone
     * @return ReportCollection\Libs\DateParser ou false
     */
    public static function parse($input, $format = null, $timezone = 'UTC')
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        // Limpa as informações de debug
        self::$instance->debug = [
            'type'      => null,
            'method'    => null,
            'parser'    => null,
            'original'  => null,
            'fixed'     => null,
            'returned'  => null,
            'error'     => null
        ];

        self::$instance->debug['original'] = $input;

        // Já é um timestamp!
        if (self::$instance->isUnixTimeStamp($input) == true) {
            self::$instance->timestamp = $input;
            self::$instance->debug['type'] = 'unixtimestamp';
            self::$instance->debug['returned'] = $input;
            $result = true;
        } else {
            if ($format != null) {
                $result = self::$instance->parseFormat($input, $format, $timezone);
            } else {
                $result = self::$instance->parseAuto($input, $timezone);
            }
        }

        return $result == false ? false : self::$instance;
    }

    private function parseFormat($input, $format = null, $timezone = 'UTC')
    {
        $this->debug['method'] = 'format';

        // String não numérica
        if (is_string($input) && is_numeric($input) == false) {
            $this->debug['parser'] = 'php';
            $this->debug['type'] = 'string';
            $value = \DateTime::createFromFormat($format, $input, new \DateTimeZone($timezone));
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
                $this->debug['type'] = 'exceltimestamp';
                $this->debug['returned'] = $this->timestamp;
                return true;
            }
        }

        return false;
    }

    private function parseAuto($input, $timezone = null)
    {
        $this->debug['method'] = 'auto';

        // String não numérica
        if (is_string($input) && is_numeric($input) == false) {
            $this->debug['parser'] = 'php';
            $this->debug['type'] = 'string';
            $input = str_replace(['/', '.'], '-', $input);
            $this->debug['fixed'] = $input;

            $value = $this->toTime($input, $timezone);
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
                $this->debug['type'] = 'exceltimestamp';
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
                && ( (int) $timestamp <=  PHP_INT_MAX)
                && ( (int) $timestamp >= ~PHP_INT_MAX)
                // Timestamps unix são contados a partir de 01-01-1970.
                && ( (int) (date('Y', $timestamp)) > 1970);
    }

    /**
     * Devolve o timestamp resultante do processo de interretação da data.
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Devolve o timestamp resultante do processo de interretação da data
     * em formato de data serial do excel.
     * @return float
     */
    public function getExcel()
    {
        return ExcelDate::timestampToExcel($this->timestamp);
    }

    /**
     * Devolve o timestamp resultante do processo de interretação da data
     * em formato de objeto
     * @return \DateTime
     */
    public function getDateObject()
    {
        return new \DateTime($this->timestamp);
    }
}
