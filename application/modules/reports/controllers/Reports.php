<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('reports_mdl');
		$this->module="reports";
		$this->title="Reports";
		$this->filters=Modules::run('filters/sessionfilters');
        //doesnt require a join on ihrisdata
        $this->ufilters=Modules::run('filters/universalfilters');
        // requires a join on ihrisdata with district level
        $this->distfilters=Modules::run('filters/districtfilters');


	}

	public function index(){

		//$data['requests']=$this->requests;
		$data['title']=$this->title;
		$data['uptitle']="Reports";
		
		$data['view']='reports';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function rosterRate(){

		//$data['requests']=$this->requests;
		$data['title']=$this->title;
		$data['uptitle']="Duty Roster Reporting";
		
		$data['view']='roster_rate';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function attendanceRate(){

		
		$data['title']='Attendance Reporting Rate';
		$data['uptitle']="Attendance Reporting";
		$data['view']='attendance_rate';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function attendroster(){

		
		$data['title']='Attendance vs Duty Roster';
		$data['uptitle']="Attendance Reporting";
		$data['view']='roster_att';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}


	public function graphData(){
		
		 
         $data=$this->reports_mdl->getgraphData();
	return $data;
	}
	public function dutygraphData(){
		$data=$this->reports_mdl->dutygraphData();
   return $data;
   }

	public function  attroData(){
		$data=$this->reports_mdl->attroData();
     //print_r($data);
	echo  json_encode($data,JSON_NUMERIC_CHECK);
	}

	public function  person_attendance_all(){
		$data=$this->reports_mdl->person_attendance_all();
        print_r($data);
		
	
	}
	public function average_hours($syear=FALSE){		
		$data['title']='Average Hours';
		$data['uptitle']="Average Monthly Hours";
		$data['view']='average_hours';
		$data['module']=$this->module;
		$facility= $_SESSION['facility'];

		$year=$this->input->post('year');
      if(!empty($year)){
        $fyear=$this->input->post('year');
        
       }
	   else{
		$fyear=$syear;
	   }
         
        $this->load->library('pagination');
        $config=array();
        $config['base_url']=base_url()."employees/viewTimeLogs";
        $config['total_rows']=$this->db->query("SELECT pid FROM clk_diff WHERE facility_id='$facility' group by date_format(date,'%Y-%m')")->num_rows();
        $config['per_page']=200; //records per page
        $config['uri_segment']=3; //segment in url  
        //pagination links styling
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = ['class' => 'page-link'];
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['use_page_numbers'] = false;
        $this->pagination->initialize($config);
        $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits 
        $data['links']=$this->pagination->create_links();
	    $data['hours']=$this->reports_mdl->average_hours($fyear);

		echo Modules::run('templates/main', $data);

		
	 }


	



	


}
