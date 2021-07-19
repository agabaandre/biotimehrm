<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Calendar_model extends CI_Model {

	public  function __construct(){
		
		
		parent:: __construct();

		$this->district_id=$this->session->userdata['district_id'];
        $this->facility_id=$this->session->userdata['facility'];
        $this->department_id="";
        $this->division="";
        $this->section="";
        $this->unit="";
       


	}

	Public function getattEvents()
	{       
		$facility_id=$this->facility_id;
        $department_id=$this->department_id;
        $division=$this->division;
        $unit=$this->unit;
        $section=$this->section;
		if(!empty($facility_id)){
            $facility=" actuals.facility_id='$facility_id'";
        }
        else{
            $facility="";
        }
        
        if(!empty($department_id)){
            $department="and ihrisdata.department_id='$department_id'";
        }
        else{
            $department="";
        }
        if(!empty($division)){
            $division="and ihrisdata.division='$division'";
        }
        else{
            $division="";
        }
        if(!empty($section)){
            $section="and ihrisdata.section='$section'";
        }
        else{
            $section="";
        }

        if(!empty($unit)){
            $unit="and ihrisdata.unit='$unit'";
        }
        else{
            $unit="";
        }
			$start=$_GET["start"];
			$end=$_GET["end"];
            $filters= $facility.' '.$department.' '.$division.' '.$section.' '.$unit;
			$query=$this->db->query("SELECT entry_id as id, actuals.end,actuals.ihris_pid as person_id,schedules.schedule as duty,actuals.facility_id,
           CONCAT(COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
			
			) as title,actuals.color,actuals.date as start,actuals.schedule_id as schedule FROM actuals,ihrisdata,schedules WHERE ($filters and ihrisdata.ihris_pid=actuals.ihris_pid AND actuals.schedule_id=schedules.schedule_id AND  actuals.date BETWEEN '$start' AND '$end') ORDER BY salary_grade ASC, date DESC");	
		  
	return $query->result();

	}
/*Read the data from DB */
	Public function getEvents()
	{
		$facility_id=$this->facility_id;
        $department_id=$this->department_id;
        $division=$this->division;
        $unit=$this->unit;
        $section=$this->section;
		if(!empty($facility_id)){
            $facility=" duty_rosta.facility_id='$facility_id'";
        }
        else{
            $facility="";
        }
        
        if(!empty($department_id)){
            $department="and ihrisdata.department_id='$department_id'";
        }
        else{
            $department="";
        }
        if(!empty($division)){
            $division="and ihrisdata.division='$division'";
        }
        else{
            $division="";
        }
        if(!empty($section)){
            $section="and ihrisdata.section='$section'";
        }
        else{
            $section="";
        }

        if(!empty($unit)){
            $unit="and ihrisdata.unit='$unit'";
        }
        else{
            $unit="";
        }
            $filters= $facility.' '.$department.' '.$division.' '.$section.' '.$unit;


            $start=$_GET["start"];
			$end=$_GET["end"];
			
			$query = $this->db->query("SELECT entry_id as id,duty_rosta.end,duty_rosta.ihris_pid as person_id,schedules.schedule as duty,CONCAT(
				COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
				
			) as title,duty_rosta.color,duty_rosta.duty_date as start,duty_rosta.schedule_id as schedule FROM duty_rosta,ihrisdata,schedules WHERE ( $filters AND ihrisdata.ihris_pid=duty_rosta.ihris_pid AND duty_rosta.schedule_id=schedules.schedule_id  AND duty_rosta.duty_date BETWEEN '$start' AND '$end' ) ORDER BY  salary_grade ASC,duty_date DESC");
	$data=$query->result();
    return $data;

	}


/*Create new events */
Public function addEvent()
	{


$department=$this->department;

$start =strtotime($_POST['start']); // or your date as well
$end=strtotime($_POST['end']);

$datediff = $end - $start;

$days=floor($datediff / (60 * 60 * 24));


if($days>1)
{

	for($i=0;$i<$days;$i++){

$oneday="+".$i." day";//1 day
$twodays="+".($i+1)." day";//1 other day for end date

$sdate = $start;

$newstartdate = date('Y-m-d',strtotime($oneday, $sdate)); //add one to prev date 2017-11-02

$newenddate = date('Y-m-d',strtotime($twodays, $sdate)); //add one to prev date
	
	$entry=$newstartdate.$_POST['hpid'];

	$facility=$this->session->userdata['facility'];


$sql = "INSERT INTO duty_rosta  (entry_id,facility_id,department_id,ihris_pid,schedule_id,color,duty_date,duty_rosta.end) VALUES (?,?,?,?,?,?,?)";



$done=$this->db->query($sql, array($entry,$facility,$department, $_POST['hpid'],$_POST['duty'],$_POST['color'],$newstartdate,$newenddate));

	


}//for

if($done){

$rows=$this->db->affected_rows();
}


else if(!$done){
    
    $rows=0;
}


return $rows;


}//if DAYS >1

else{



	$sql = "INSERT INTO duty_rosta (entry_id,facility_id,department_id,ihris_pid,schedule_id,color,duty_date,duty_rosta.end) VALUES (?,?,?,?,?,?,?)";

	$entry=date("Y-m-d", strtotime($_POST['start'])).$_POST['hpid'];

	$facility=$this->session->userdata['facility'];



	$done=$this->db->query($sql, array($entry,$facility,$department, $_POST['hpid'],$_POST['duty'],$_POST['color'],$_POST['start'],$_POST['end']));



if($done){
$rows=$this->db->affected_rows();
}


else if(!$done){
    
    $rows=0;
}

return $rows;


}//end else
	}// end add event

	/*Update  event */

	Public function updateEvent()
	{

	$sql = "UPDATE duty_rosta SET ihris_pid = ?, schedule_id = ?, color = ? WHERE entry_id = ?";

	$this->db->query($sql, array($_POST['hpid'],$_POST['duty'], $_POST['color'], $_POST['id']));
		
		return ($this->db->affected_rows()!=1)?false:true;
	}


	/*Delete event */

	Public function deleteEvent()
	{

	$sql = "DELETE FROM duty_rosta WHERE entry_id = ?";

	$this->db->query($sql, array($_GET['id']));
		return ($this->db->affected_rows()!=1)?false:true;
	}

	/*Update  event */

	Public function dragUpdateEvent()
	{
			//$date=date('Y-m-d h:i:s',strtotime($_POST['date']));		
			
$start =strtotime($_POST['start']); // or your date as well
 $end=strtotime($_POST['end']);

$datediff = $end - $start;

$days=floor($datediff / (60 * 60 * 24));


if($days>1)
{

	for($i=0;$i<$days;$i++){

$oneday="+".$i." day";//1 day
$twodays="+".($i+1)." day";//1 other day for end date

$sdate = $start;

$newstartdate = date('Y-m-d',strtotime($oneday, $sdate)); //add one to prev date 2017-11-02

$newenddate = date('Y-m-d',strtotime($twodays, $sdate)); //add one to prev date

	$sql = "UPDATE duty_rosta SET  duty_date = ?, end = ?   WHERE entry_id= ?";
			$this->db->query($sql, array($newstartdate,$newenddate, $_POST['id']));

	


}//for


}//if


else{

			$sql = "UPDATE duty_rosta SET  duty_date = ?, end = ?   WHERE entry_id= ?";
			
			$this->db->query($sql, array($_POST['start'],$_POST['end'], $_POST['id']));
			
}

		return ($this->db->affected_rows()!=1)?false:true;


	}

Public function addleaveEvent()
	{


$start =strtotime($_POST['start']); // or your date as well


$i=1;

$oneday="+".$i." day";//1 day


$end= date('Y-m-d',strtotime($oneday, $start)); //add one to prev 


	
	$entry=$start.$_POST['hpid'];

	$facility=$this->session->userdata['facility'];
	$department=$this->department;

$data=array(
	'entry_id'=>$entry,
	'facility_id'=>$facility,
	'department_id'=>$department,
	'ihris_pid'=>$_POST['hpid'],
	'schedule_id'=>$_POST['duty'],
	'color'=>$_POST['color'],
	'duty_date'=>$_POST['start'],
	'end'=>$end );
$sql = $this->db->replace('leave_rota',$data);

	

if($sql){

$rows=$this->db->affected_rows();
}


else if(!$sql){
    
    $rows=0;
}


return $rows;


 }//end add

}








