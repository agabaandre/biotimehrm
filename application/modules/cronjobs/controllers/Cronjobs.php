<?php
date_default_timezone_set('Africa/Kampala');
class Cronjobs extends MX_Controller{

public function importMachineCSv(){
  // uncomment and set a custom file to upload
  //  $currentfile="2019-10-01.csv";
    
    
    //comment the two lines below to if a custom file is set above
    // get yesterday's file
    $fdate=date('Y-m-d',strtotime("-1 days"));
    $currentfile=$fdate.".csv";
  
    //this is the location of the file to be uploaded 
    $filename='/home/mohhr/attendance/hq/uploaded/headquarters_'.$currentfile;

if(file_exists($filename))  //check whether file has data
  {

$file = fopen($filename, "r");
$count = 0;  
$allData=array();
$rowsnow=0;
while ($machineData = fgetcsv($file, 100000, ","))
{
$count++;
$rowsnow++;
if($count>1):
 $person_id= $machineData[0];
 $newihris=$person_id;
$this->db->select('ihris_pid,ihris_pid,facility_id');
$this->db->where('ihris_pid', $person_id);
$id=$this->db->get('ihrisdata');
$dataids=$id->result();
foreach($dataids as $dataid){
    
     $facility_id=$dataid->facility_id;
     
     $ihris_pid=$dataid->ihris_pid;
 

    
}


///////start processing data

$clockin='';         
$clockout='';  
   
   //date_default_timezone_set("Africa/Kampala");

$time_in=$machineData[2]; //rawtimein

$time_out=$machineData[3]; //raw timeout

if(!empty($time_in)){
  
$time_in=@date_create($machineData[2]); //rawtimein

$clockin=@date_format($time_in,"H:i"); //converted clock in time
}
else{
  $clockin=='NULL';
  
}



if(!empty($time_out)){
$time_out=@date_create($machineData[3]);
$clockout=@date_format($time_out,"H:i"); //converted clock out time

}
else{
$clockout=='NULL';
}
//1527552000
 
//$facility_id=$machineData[4]; 
 
$mydate=@date_create($machineData[5]);

$date=@date_format($mydate,"Y-m-d"); 

$now=date('Y-m-d h:i:sa');



$entryid=$date.$ihris_pid;
         
$excel_data=array('ihris_pid'=>$person_id,'time_in'=>$clockin, 'time_out'=>$clockout ,'date'=>$date , 'facility_id'=>$facility_id, 'lastup'=>$now);
 
 array_push($allData,$excel_data);

endif;
}
 $insert=$this->db->insert_batch('clk_log',$allData);
 
  if($insert){
         echo  $msg="<font color='green'> ".($rowsnow-1). "  records Imported successfully</font><br>";
             }
          else{
              
         echo   $msg="<font color='red'> Import unsuccessful</font><br>";
              
    }
  }
  else{
  
  
    echo $msg="<font color='red'>No Valid Data in FIle found on the Server Today</font><br>";
    
  }
}



//Annual
public function publicdaystoAttend(){
  ignore_user_abort(true);
  ini_set('max_execution_time',0);
  //uncomment and set $year on line 195
  //$year="";
  //comment below(197) if $year above(195) is set
  $year=date('Y');

  

 if(!empty($year)){
// $query=$this->db->query("DELETE from actuals where schedule_id='27' and actuals.date like'$year%'");
$query=$this->db->query("REPLACE INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, actuals.date, actuals.end)
 SELECT DISTINCT CONCAT(holidaydate,ihrisdata.ihris_pid) AS entry_id, ihrisdata.facility_id,ihrisdata.department_id, ihrisdata.ihris_pid,schedules.schedule_id, 
schedules.color,holidaydate as duty_date, DATE_ADD(holidaydate, INTERVAL 1 DAY) from public_holiday,ihrisdata,schedules 
WHERE schedules.schedule_id=27 and year='$year' and CONCAT(holidaydate,ihrisdata.ihris_pid) NOT IN (SELECT entry_id from actuals) AND ihrisdata.facility_id='facility|787'") ; 

$rowsnow=$this->db->affected_rows();
if($query){
       echo  $msg="<font color='green'>".$rowsnow. " Public Holiday records set in attendance</font><br>";
           }
        else{
            
       echo   $msg="<font color='red'>No public holidays set in attendance</font><br>";
            
}
$this->log($msg);

}
$this->addHolidays();
}

public function addHolidays(){

  //define year
  //$year="";
  //predefined year
  $year=date('Y');
$url="https://calendarific.com/api/v2/holidays?&api_key=a78b8ab5df85afb0cb3666cbc30580a5d74f0f7e&country=UG&year=.'$year'.";
$data = file_get_contents($url); 
$output=json_decode($data);                                                   
//print_r($output);
$holidays=($output->response->holidays);
$indata=array();
$count=0;

   
foreach ($holidays as $holiday){
    $count++;
    $name=$holiday->name;
    $date=$holiday->date->iso;
    $year=$holiday->date->datetime->year;
    $type=$holiday->type[0];

  

$insert=array(
    'id'=>$date.$name,
    'holiday_name'=>$name,
    'type'=>$type,
    'holidaydate'=>$date,
    'year'=>$year

);
   

array_push($indata,$insert);
       
}

$insert=$this->db->insert_batch('public_holiday',$indata);

  $rowsnow=$this->db->affected_rows();
  if($insert){
       echo  $msg="<font color='green'>".$rowsnow. " Public Added</font><br>";
           }
        else{
            
       echo   $msg="<font color='red'>No public holidays Added</font><br>";
      
}
$this->log($msg);   
//remove seasons

$this->db->where('type', 'season');
$delete=$this->db->delete('public_holiday');
$rowsnow=$this->db->affected_rows();
if($delete){
     echo  $msg="<font color='red'>".$rowsnow. " Seasons Removed</font><br>";
         }
      else{
          
     echo   $msg="<font color='red'>No Seasons</font><br>";
          
}
//print_r($indata);
$this->log($msg);   


}
public function getMohEmployees(){

$qry=$this->db->query("SELECT distinct ihris_pid,department_id,facility_id FROM ihrisdata WHERE ihrisdata.facility_id IN ('facility|787')");

	$employees=$qry->result();
	return $employees;
}
public function isWeekend($date) {
  $day=intval(date('N', strtotime($date)));
  return ($day>= 6);
 }
 
 //duty roster moh
public function AutoMohRoster(){

  date_default_timezone_set('Africa/Kampala');
  ignore_user_abort(true);
  ini_set('max_execution_time',0);
  
  $employees=$this->getMohEmployees();
  
  $year=date('Y');
  
  foreach($employees as $employee){
  
  
  for($m=1;$m<=12;$m++){
      
  $month_days = cal_days_in_month(CAL_GREGORIAN, $m,$year); 
  
  for($d=1;$d<=$month_days;$d++){//
  
  $dDate = $year."-".$m."-".$d;
  $date = strtotime($dDate);
  $dayDate= date('Y-m-d', $date);
  $hris_pid=$employee->ihris_pid;
  $facility_id=$employee->facility_id;
  $entry_id=$dayDate.$hris_pid;
  $department_id=$employee->department_id;
  $duty_date=$dayDate;
  $tommorodate=date_create($dayDate);
  date_add($tommorodate,date_interval_create_from_date_string("1 days"));
  $end=date_format($tommorodate,'Y-m-d');
  //weekend for Ministry of health only
  if ($this->isWeekend($dayDate)){
      
      $duty="17";
      $color='#d1a110';
  }
  else{
  
    $duty="14";
    $color="#297bb2";
  }
  
  $data = array(
    'entry_id' =>$entry_id ,
    'facility_id'=>$facility_id,
    'department_id'=>$department_id,
    'ihris_pid'=>$hris_pid,
    'schedule_id'=>$duty,
    'color'=>$color,
    'duty_date'=>$duty_date,
    'end'=>$end,
    'allDay'=>'true'
  
   );
  
    $query=$this->db->replace('duty_rosta',$data);
  
  }//
  
  
  }
  
  }//3000
   $message="Created ".$this->db->affected_rows();
   $this->log($message);
  
  }

  //duty roster for other facilities
  public function getOtherEmployees(){

    $qry=$this->db->query("SELECT distinct ihris_pid,department_id,facility_id FROM ihrisdata WHERE ihrisdata.facility_id IN (SELECT facility_id from clk_log where facility_id='facility|787')");
    
      $employees=$qry->result();
      return $employees;
    }
   
     
    public function AutoRosterOthers(){
    
      date_default_timezone_set('Africa/Kampala');
      ignore_user_abort(true);
      ini_set('max_execution_time',0);
      
      $employees=$this->getOtherEmployees();
      
      $year=date('Y');
      
      foreach($employees as $employee){
      
      
      for($m=1;$m<=12;$m++){
          
      $month_days = cal_days_in_month(CAL_GREGORIAN, $m,$year); 
      
      for($d=1;$d<=$month_days;$d++){//
      
      $dDate = $year."-".$m."-".$d;
      $date = strtotime($dDate);
      $dayDate= date('Y-m-d', $date);
      $hris_pid=$employee->ihris_pid;
      $facility_id=$employee->facility_id;
      $entry_id=$dayDate.$hris_pid;
      $department_id=$employee->department_id;
      $duty_date=$dayDate;
      $tommorodate=date_create($dayDate);
      date_add($tommorodate,date_interval_create_from_date_string("1 days"));
      $end=date_format($tommorodate,'Y-m-d');
      //weekend for Ministry of health only
      if ($dayDate){
          
        $duty="14";
        $color="#297bb2";
      }
  
      
      $data = array(
        'entry_id' =>$entry_id ,
        'facility_id'=>$facility_id,
        'department_id'=>$department_id,
        'ihris_pid'=>$hris_pid,
        'schedule_id'=>$duty,
        'color'=>$color,
        'duty_date'=>$duty_date,
        'end'=>$end,
        'allDay'=>'true'
      
       );
      
        $query=$this->db->replace('duty_rosta',$data);
      
      }//
      
      
      }
      
      }//3000
       $message="Created ".$this->db->affected_rows();
       $this->log($message);
      
      }
    


public function requesttoActuals(){ 
  //coming soon after data is used
  $query=$this->db->get('requests');
  $data=$query->result();
  foreach ($data as $row){
    //add some logic here when real data is available
    
  }
 
}

public function dutySums()
{

    $ymonth="";
    //$ymonth=$_GET['month'];
    if($ymonth){
      $ymonth;
    }
    else{
        //last month
        $ymonth = date('Y-m', strtotime('-1 months'));
    }
   
   
     if($ymonth){
        
     ini_set('max_execution_time',0);
     ignore_user_abort(true);
     $sql=$this->db->query("SET @p0='$ymonth'");
     $sql=$this->db->query("TRUNCATE TABLE dutysummary");
     $sql=$this->db->query("CALL `duty_sums`(@p0)");
     if($sql){
         echo "Procedure Executed Succesfully";
     }
     else{
         echo "Procedure Execution Failed";
     }
    }
}
public function attSums()
{
   
    $ymonth="";
    //$ymonth=$_GET['month'];
    if($ymonth){
      $ymonth;
    }
    else{
        //last month
        $ymonth = date('Y-m', strtotime('-1 months'));
    }
   
     if($ymonth){
     ini_set('max_execution_time',0);
     ignore_user_abort(true);
     $sql=$this->db->query("SET @p0='$ymonth'");
     $sql=$this->db->query("TRUNCATE TABLE att_summary");
     $sql=$this->db->query("CALL `att_proc`(@p0)");
     if($sql){
         echo "Procedure Executed Succesfully";
     }
     else{
         echo "Procedure Execution Failed";
     }
    }
}

//render duty_rostercsv
public function renderdutyCsv()
    {
		ini_set('max_execution_time',0);
		ignore_user_abort(true);
		$filename='mohduty_summary'.'.csv';
		$filejson='mohduty_summary'.'.json';
        
        $this->db->select('person_id,dutydate,wdays,offs,mleave,other');
        $this->db->from('dutysummary');
        $qry= $this->db->get();
        $tbdata=$qry->result_array();

		$file = fopen('/home/mohhr/mohattshares/'.$filename, 'w+');
		$file2 = fopen('/home/mohhr/mohattshares/'.$filejson, 'w+');  

		$i=0;

		foreach ($tbdata as $data) {
			fputcsv($file, $data);
			fwrite($file2, json_encode($data, JSON_PRETTY_PRINT)); 

			$i++;
		}
		fclose($file);
		fclose($file2);
    echo $msg=  'CSV and Json for rosta generated Successfully';
	//print_r($tbdata);
	return $tbdata;

	}

  //render attendance csv
	public function renderattCsv()
    { 
		ini_set('max_execution_time',0);
		ignore_user_abort(true);
		$filename='mohatt_summary'.'.csv';
		$filejson='mohatt_summary'.'.json';
        
        $this->db->select('ihris_pid,rdate,present,offduty,official,leaves');
        $this->db->from('att_summary');
        $qry= $this->db->get();
        $tbdata=$qry->result_array();

		$file = fopen('/home/mohhr/mohattshares/'.$filename, 'w+');
		$file2 = fopen('/home/mohhr/mohattshares/'.$filejson, 'w+');  

		$i=0;

		foreach ($tbdata as $data) {
			fputcsv($file, $data);
			fwrite($file2, json_encode($data, JSON_PRETTY_PRINT)); 

			$i++;
		}
		fclose($file);
		fclose($file2);
        echo $msg = 'CSV and Json for attendance generated Successfully';
   // print_r($tbdata);
   //return $tbdata;

	}
  public function log($message){
    //add double [] at the beggining and at the end of file contents
   return file_put_contents('log.txt', "\n{".'"REQUEST DETAILS: '.date('Y-m-d H:i:s').' Time": '.json_encode($message).'},',FILE_APPEND);
}



}
?>
