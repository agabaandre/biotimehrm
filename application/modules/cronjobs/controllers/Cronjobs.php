<?php

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

public function biotimeClockin(){
  $areas=$this->db->get('biotime_devices')->result();
  foreach($areas as $area){
  $query=$this->db->query("REPLACE INTO clk_log (
    entry_id,
    ihris_pid,
    facility_id,
    time_in,
    date,
    location,
    source,
    facility)
    SELECT
    
   DISTINCT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as entry_id,
    ihrisdata.ihris_pid,
    facility_id, 
    punch_time,
    DATE(biotime_data.punch_time) as date,
    area_alias,
    'BIO-TIME',
    ihrisdata.facility
    from  biotime_data, ihrisdata where biotime_data.area_alias='$area->area_name' AND (biotime_data.emp_code=ihrisdata.card_number OR biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state='Check In' OR punch_state='0') ");
   
echo $area->area_name. " Checkin " .$this->db->affected_rows();
  }
}
public function biotimeClockout(){

 $query=$this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as entry_id,punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state!=0 OR punch_state='Check Out') AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT entry_id from clk_log) ");
 $entry_id=$query->result();

 foreach($entry_id as $entry){

$this->db->set('time_out', $entry->punch_time);
$this->db->where('entry_id', $entry->entry_id);
$query=$this->db->update('clk_log');

}
echo $this->db->affected_rows();
  
}

public function markAttendance(){

//poplulate actuals
$query=$this->db->query("INSERT INTO actuals( entry_id, facility_id, department_id, ihris_pid, schedule_id, color,
 actuals.date, actuals.end,stream ) SELECT DISTINCT CONCAT( clk_log.date, ihrisdata.ihris_pid ) AS entry_id, ihrisdata.facility_id, 
 ihrisdata.department, ihrisdata.ihris_pid, schedules.schedule_id, schedules.color, clk_log.date, DATE_ADD(date, INTERVAL 01 DAY),channel FROM ihrisdata, 
 clk_log, schedules WHERE ihrisdata.ihris_pid = clk_log.ihris_pid AND schedules.schedule_id =22 AND CONCAT( clk_log.date, ihrisdata.ihris_pid )
  NOT IN (SELECT entry_id from actuals)");        
  
  $rowsnow=$this->db->affected_rows();
  if($query){
         echo  $msg="<font color='green'>".$rowsnow. "  Attendance Records Marked</font><br>";
             }
          else{
              
         echo   $msg="<font color='red'>Failed to Mark</font><br>";
              
}
}
//monthly
public function rostatoAttend(){
  //To set custom month uncomment below and set  ymonth of choice
  //$ymonth="2019-08"."-";
  // comment  the file below on line 145 if custom ymonth is set.
  $ymonth=date('Y-m')."-";

  
 if(!empty($ymonth)){ 

  //poplulate actuals
  $query=$this->db->query("INSERT INTO actuals( entry_id, facility_id, department_id, ihris_pid, schedule_id, color, actuals.date, actuals.end ) 
  SELECT entry_id,facility_id,department_id,ihris_pid,schedule_id,color,duty_rosta.duty_date,duty_rosta.end from duty_rosta WHERE schedule_id 
  IN(17,18,19,20,21) and duty_rosta.duty_date like '$ymonth%' AND duty_rosta.entry_id NOT IN(SELECT entry_id from actuals)");
  $rowsnow=$this->db->affected_rows();
  if($query){
    echo  $msg="<font color='green'>".$rowsnow. "  Attendance Records Marked</font><br>";
        }
     else{
         
    echo   $msg="<font color='red'>Failed to Mark</font><br>";
         
}
}
  
  $query=$this->db->query("Update actuals set schedule_id='25', color='#29910d' WHERE schedule_id IN(18,19,20,21)");
  
    $rowsnow=$this->db->affected_rows();
    if($query){
           echo  $msg="<font color='green'>".$rowsnow. "  Leave records recognised by attendance </font><br>";
               }
            else{
                
           echo   $msg="<font color='red'>No leave records found</font><br>";
                
  }

  $query=$this->db->query("Update actuals set schedule_id='24', color='#d1a110' WHERE schedule_id='17'");
  
    $rowsnow=$this->db->affected_rows();
    if($query){
           echo  $msg="<font color='green'>".$rowsnow. "  Offduty records recognised by attendance </font><br>";
               }
            else{
                
           echo   $msg="<font color='red'>No Off duty records found</font><br>";
                
  }
   
  

}
//Annual
public function publicdaystoAttend(){
  //uncomment and set $year on line 195
  //$year="";
  //comment below(197) if $year above(195) is set
  $year=date('Y');

  

 if(!empty($year)){
$query=$this->db->query("DELETE from actuals where schedule_id='27' and actuals.date like'$year%'");
$query=$this->db->query("INSERT INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, actuals.date, actuals.end)
 SELECT DISTINCT CONCAT(holidaydate,ihrisdata.ihris_pid) AS entry_id, ihrisdata.facility_id,ihrisdata.department_id, ihrisdata.ihris_pid,schedules.schedule_id, 
schedules.color,holidaydate as duty_date, DATE_ADD(holidaydate, INTERVAL 1 DAY) from public_holiday,ihrisdata,schedules 
WHERE schedules.schedule_id=27 and year='$year' and CONCAT(holidaydate,ihrisdata.ihris_pid) NOT IN (SELECT entry_id from actuals)"); 

$rowsnow=$this->db->affected_rows();
if($query){
       echo  $msg="<font color='green'>".$rowsnow. " Public Holiday records set in attendance</font><br>";
           }
        else{
            
       echo   $msg="<font color='red'>No public holidays set in attendance</font><br>";
            
}

}
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

}
public function getAllEmployees(){

	$this->db->select('ihris_pid,department_id,facility_id');
	$qry=$this->db->get('ihrisdata');

	$employees=$qry->result();
	return $employees;
}
public function isWeekend($date) {
  $day=intval(date('N', strtotime($date)));
  return ($day>= 6);
 }
 
public function autoFillRosta(){

  date_default_timezone_set('Africa/Kampala');
  ignore_user_abort(true);
  ini_set('max_execution_time',0);
  
  $employees=$this->getAllEmployees();
  
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
  
    $this->db->replace('duty_rosta',$data);
  
  }//
  
  
  }
  
  }//3000
  }


public function requesttoActuals(){ 
  //coming soon after data is used
  $query=$this->db->get('requests');
  $data=$query->result();
  foreach ($data as $row){
    //add some logic here when real data is available
    
  }
  
  print_r($data);

}

}
?>
