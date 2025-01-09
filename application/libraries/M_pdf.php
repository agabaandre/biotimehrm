<?php
use Mpdf\Mpdf;

class M_pdf {
    protected $mpdf;

    public function __construct($params = []) {
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4-L',
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
