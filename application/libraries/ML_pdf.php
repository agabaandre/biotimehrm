<?php
use Mpdf\Mpdf;

class ML_pdf {
    protected $mpdf;

    public function __construct($params = []) {
        // Set default configuration with landscape format
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4-L', // Use 'A4-L' for landscape format
            'default_font' => 'Arial'
        ];

        $config = array_merge($defaultConfig, $params);

        $this->mpdf = new Mpdf($config);
    }

    public function loadHtml($html) {
        $this->mpdf->WriteHTML($html);
    }

    public function output($filename = 'document.pdf', $destination = 'I') {
        return $this->mpdf->Output($filename, $destination);
    }

    public function getMpdfInstance() {
        return $this->mpdf;
    }
}
