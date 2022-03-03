<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->department=$this->session->userdata['department_id'];

	}



	public function getFacilities($district_id)
	{

		$this->db->select('distinct(facility),facility_id');
		$this->db->where('district_id',$district_id);
		$query=$this->db->get('ihrisdata');

		return $query->result();
 
	}
	public function getgraphData(){
		$facility=$_SESSION['facility'];

		$date_from=date("Y-m",strtotime("-11 month"));
		$date_to=date('Y-m');
		
	
	    $datas=array();
		$period=array();
		$targets=array();
		$target=$this->db->query("SELECT staff from staffing_rate WHERE date like '$date_to%' AND facility_id='$facility'");
	foreach($target->result() as $dt):
		$staff=$dt->staff;
	endforeach;
		$query=$this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from attendance_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
	foreach($query->result() as $data):
	  array_push($targets,$staff);
	  array_push($period,$data->period);
	  array_push($datas, $data->data);

	 endforeach;



	  return array('period'=>$period, 'data'=>$datas, 'target'=>$targets);

	}

	public function dutygraphData(){
		$facility=$_SESSION['facility'];
		$date_from=date("Y-m",strtotime("-11 month"));
		$date_to=date('Y-m');
	    $datas=array();
		$period=array();
		$targets=array();
		$target=$this->db->query("SELECT staff from staffing_rate WHERE date like '$date_to%' AND facility_id='$facility'");
	foreach($target->result() as $dt):
		$staff=$dt->staff;
	endforeach;
		$query=$this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from rosta_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
	foreach($query->result() as $data):
	  array_push($targets,$staff);
	  array_push($period,$data->period);
	  array_push($datas, $data->data);

	 endforeach;



	  return array('period'=>$period, 'data'=>$datas, 'target'=>$targets);

	}
	public function attroData(){
		$facility=$_SESSION['facility'];
		$date_from=date("Y-m",strtotime("-11 month"));
		$date_to=date('Y-m');
	    $rdata=array();
		$rperiod=array();
		$adata=array();
		$aperiod=array();
		
	  $query=$this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from dutydays_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
	foreach($query->result() as $data):
		$rdate=$data->period;
		$rostadata=$data->data;
		array_push($rdata,$rostadata);
		array_push($rperiod,$rdate);
	    $query2=$this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from presence_rate WHERE date='$rdate' AND facility_id='$facility'");
		foreach($query2->result() as $attd):
		$attdate=$attd->period;
		$attdata=$attd->data;	
		array_push($adata,$attdata);
		array_push($aperiod,$attdate);
		endforeach;
	 endforeach;



	  return array('aperiod'=>$aperiod, 'adata'=>$adata,'dperiod'=>$rperiod,'ddata'=>$rdata);

	}
	public function average_hours($fyear){
		$facility = $_SESSION['facility'];
		if(!empty($fyear)){
			
			$filter="and date_format(date,'%Y')='$fyear'";

		}
		else{
			$filter="";
		}
		$fac=$this->db->query("SELECT (SUM(time_diff)/COUNT(pid)) as avg_hours,facility,date_format(date,'%Y-%m') as month_year FROM clk_diff WHERE facility_id='$facility' $filter group by date_format(date,'%Y-%m')")->result_array();
        return $fac;
	}




	



	


}
