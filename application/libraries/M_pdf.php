<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Load Composer's autoload
require_once APPPATH . '../vendor/autoload.php';

use Mpdf\Mpdf;

class M_pdf {
    public $pdf;

    public function __construct($params = []) {
        // Default PDF settings
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4-L', // Landscape Mode
            'default_font' => 'Arial'
        ];

        // Merge user params with defaults
        $config = array_merge($defaultConfig, $params);

        // Initialize Mpdf
        $this->pdf = new Mpdf($config);
    }

    public function loadHtml($html) {
        $this->pdf->WriteHTML($html);
    }

    public function output($filename = 'document.pdf', $destination = 'I') {
        return $this->pdf->Output($filename, $destination);
    }

    public function getInstance() {
        return $this->pdf;
    }
}
?>
