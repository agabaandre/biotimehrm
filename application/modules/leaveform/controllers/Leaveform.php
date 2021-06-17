<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leaveform extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('Leaveform_mdl','leavedatahandler');


	}
	
    public function leaveApplication($request_id=null){    
    //   $ihris_id=urldecode($person_id);
	  $data['employee']=$this->leavedatahandler->getLeave($request_id);

	   $this->load->library('M_pdf');

	   $html=$this->load->view('leaveform',$data,true);
       $date=date('Y-m-d');
	   $filename="leave_application_".$date.".pdf";

	   $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

	   $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
	   $this->m_pdf->pdf->showWatermarkImage = true;

	   date_default_timezone_set("Africa/Kampala");
	   $this->m_pdf->pdf->SetHTMLFooter("Printed / Accessed on: <b>".date('d F,Y h:i A')."</b>");

	   $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
	   $this->m_pdf->showWatermarkImage = true;
		

	   ini_set('max_execution_time',0);

	   $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		
	   //download it D save F.
	   $this->m_pdf->pdf->Output($filename,'I');
   } 

	


}
