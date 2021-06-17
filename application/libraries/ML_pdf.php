<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');
 
include_once APPPATH.'/third_party/mpdf/mpdf.php';
 
class ML_pdf {
 
//public $param;
public $pdf;
public function __construct()
{
   // $this->param =$param;
    $this->pdf = new mPDF('utf-8', 'A4-L');
}
}

?>