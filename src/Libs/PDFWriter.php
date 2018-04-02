<?php
namespace ReportCollection\Libs;

use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class PDFWriter extends Mpdf
{
    protected function createExternalWriterInstance($config)
    {
        return new \Mpdf\Mpdf([
            'mode'        => 'utf-8', 
            //'format'    => [190, 236], 
            'orientation' => 'L',
            ]);
    }
}