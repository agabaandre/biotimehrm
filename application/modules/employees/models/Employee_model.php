<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Employee_model extends CI_Model
{
   public  function __construct(){
        parent:: __construct();
      

    }

   

    public function get_employees($filters)
    {
        
        $query=$this->db->query("select distinct ihris_pid,surname,firstname,othername,job,telephone,mobile,department,facility,district,nin,card_number, ipps,facility_id from  ihrisdata where $filters");
        
    
        $result=$query->result();

    
      
        return $result;
    }
    

    public function get_employee($id=FALSE) {
        $this->db->where('ihris_pid', $id);
        $query = $this->db->get('ihrisdata');
        
        return $query->row();
        
    }

    public function get_employee_clock($facility, $staffId) {
        $this->db->select('status');
        $this->db->from('time_log');
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($staffId));
        $this->db->where('date', date("Y-m-d"));
        $query = $this->db->get();
        if($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function search_employees($facility, $search_term)
    {
        $this->db->select('ihris_pid, firstname, surname, job, facility_id');

        $this->db->where('ihrisdata.facility_id', urldecode($facility))
        ->like('ihris_pid', urldecode($search_term));

        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
        ->like('firstname', urldecode($search_term));

        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
        ->like('surname', urldecode($search_term));
        
        $this->db->order_by('surname', 'asc');

        
        $this->db->from('ihrisdata');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function count_employees($facility) 
    {
        $department=$this->department;
        $unit=$this->unit;
        // return $this->db->count_all_results('ihrisdata');
        if($this->department){
            $this->db->where('department_id',$this->department);
            $this->db->or_where('division',$this->division);
            $this->db->or_where('unit',$this->unit);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->from('ihrisdata');
        $qry=$this->db->get();
        return $qry->num_rows();
    }

    public function get_schedules($employeeId) 
    {
        $this->db->select('duty_date, end, allDay, starts, ends, letter');
        $this->db->from('ihrisdata');
        $this->db->join('duty_rosta', 'duty_rosta.ihris_pid = ihrisdata.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where('duty_rosta.ihris_pid', urldecode($employeeId));
        $query = $this->db->get()->result();
        return $query;
    }

    public function count_employees_present($facility) 
    {
        $this->db->select('*');
        $this->db->from('actuals');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('actuals.facility_id', urldecode($facility));
        $this->db->where('actuals.date', date("Y-m-d"));
        $this->db->where('actuals.schedule_id', '22');
        $qry=$this->db->get();
        return $qry->num_rows();
    }

    public function count_employees_off($facility)
    {
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->where('duty_rosta.schedule_id', '17');
        $this->db->where('duty_rosta.duty_date', date("Y-m-d"));
        $qry=$this->db->get();
        return $qry->num_rows();
    }

    //Staff On Duty Today
    public function count_employees_working($facility)
    {
        $work_days = array('14','15','16');
        
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        $qry=$this->db->get();
        return $qry->num_rows();
        
    }

    public function staff_working_today($facility)
    {

        $work_days = array('14','15','16');
        
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('ihrisdata', 'ihrisdata.ihris_pid = duty_rosta.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        return $this->db->get()->result_array();
    }

    public function get_schedule_types($type) 
    {
        if($type == 'attendance') 
        { 
            $purpose = 'a';
        } else if($type == 'roster'){
            $purpose = 'r';
        } else {
            
            die();
        }

        
        $this->db->select('schedule_id, schedule, letter');
        $this->db->from('schedules');
        $this->db->where('purpose', $purpose);
        return $this->db->get()->result_array();
    }

    public function insert_schedule($data) {

        $response = array();
       
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id'=> $value . '' . $data->ihris_pid,
                'facility_id'=>$data->facility_id,
                'ihris_pid'=>$data->ihris_pid,
                'schedule_id'=>$data->schedule_id,
                'duty_date'=>$value,
                'end'=> strftime("%Y-%m-%d", strtotime("$value +1 day")),
                'allDay'=> 'true',
                'color'=> $this->setColor($data->schedule_id)
            );

            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            $end_date = strftime("%Y-%m-%d", strtotime("$value +1 day"));

            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('duty_rosta');
            if($query->num_rows() == 1) {
                $this->db->query("UPDATE duty_rosta SET duty_date='".$duty_date."', end='".$end_date."' WHERE entry_id = '".$entry_id."' ");
                // $response[] = "$entry_id Updated";

                $response[] = true;

            } else {
                if($this->db->insert('duty_rosta',$duty)){
                    $response[] = true;
                }else {
                    $response[] = false;
                }

                
            }
           
        }

        return $response;
        
    }

    public function insert_attendance($data) {

        $response = array();
       
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id'=> $value . '' . $data->ihris_pid,
                'facility_id'=>$data->facility_id,
                'ihris_pid'=>$data->ihris_pid,
                'schedule_id'=>$data->schedule_id,
                'date'=>$value
            );

            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            

            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('actuals');
            if($query->num_rows() == 1) {
                $this->db->query("UPDATE actuals SET `date`='".$duty_date."' WHERE entry_id = '".$entry_id."' ");
                // $response[] = "$entry_id Updated";

                $response[] = array('status'=>'updated','entry'=>$entry_id);

            } else {
                $this->db->insert('actuals',$duty);
                // $response[] = "$entry_id Created";

                $response[] = array('status'=>'created','entry'=>$entry_id);
            }
           
        }

        return $response;
    }

    public function setColor($schedule) {

        switch ($schedule) {
            case '14':
                # Day
                $color = '#d1a110';
                break;

                case '15':
                # Evening
                $color = '#49b229';
                break;

                case '16':
                # Night
                $color = '#29b229';
                break;

                case '17':
                # Off Duty
                $color = '#297bb2';
                break;

                case '18':
                # Annual Leave
                $color = '#603E1F';
                break;

                case '19':
                # Study Leave
                $color = '#0592942';
                break;

                case '20':
                # Maternity Leave
                $color = '#280542';
                break;

                case '21':
                # Other Leave
                $color = '#420524';
                break;
            
            default:
                # code...
                break;
        }

        return $color;

    }

    public function get_employee_schedule($facility, $person) {
        $this->db->select('duty_date');
        $this->db->from('duty_rosta');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        
        $this->db->order_by('duty_date','DESC');
        
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_employee_attendance($facility, $person) {
        $this->db->select('date');
        $this->db->from('actuals');
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        
        $this->db->order_by('date','DESC');
        
        $query = $this->db->get();
        if($query->num_rows() > 0)
        {
            return $query->result();
        } else {
            return false;
        }
    }

    public function clock_in_employee($userdata){

        $data = array(
            'entry_id'=> $userdata->date . $userdata->ihris_pid,
            'ihris_pid'=> $userdata->ihris_pid,
            'facility_id'=>$userdata->facility_id,
            'time_in'=>$userdata->time_in,
            'time_out'=> '',
            'status'=>$userdata->status,
            'date'=>$userdata->date
        );

        $query = $this->db->insert('time_log', $data);
        if($query) {
                $query2 = $this->db->insert('time_log_history', array(
                'ihris_pid'=>$userdata->ihris_pid,
                'facility_id'=>$userdata->facility_id,
                'entry_id'=> $userdata->date . $userdata->ihris_pid,
                'time_in'=>$userdata->time_in, 
                'status'=>$userdata->status,
                'date'=>$userdata->date));

                if($query2) { return true; } else { return false; };
        } else {
            return false;
        }
    }

    public function clock_out_employee($userdata){

        $data = array(
            'time_out'=> $userdata->time_out,
            'status'=>$userdata->status
        );

        $entry_id = $userdata->date . $userdata->ihris_pid;

        $this->db->set($data);
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        $this->db->where('entry_id',$entry_id);
        $query = $this->db->update('time_log');
        if($query) {
            $query2 = $this->db->insert('time_log_history', array(
                'ihris_pid'=>$userdata->ihris_pid,
                'facility_id'=>$userdata->facility_id,
                'entry_id'=>$entry_id, 
                'time_out'=>$userdata->time_out, 
                'status'=>$userdata->status,
                'date'=>$userdata->date));


                if($query2) { return true; } else { return false; }
        } else {
            return false;
        }
    }


//datatable ops

    function countStaff()
    {   
        $facility=$this->facility;
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        if($facility){
            $this->db->where('facility_id',$facility);
        }

        $query = $this
                ->db
                ->where('facility_id',$facility)
                ->get('ihrisdata');
    
        return $query->num_rows();  

    }
    
    function fetchAllStaff($limit,$start,$col,$dir)
    {   
      $facility=$this->facility;

      if($this->department){
            $this->db->where('department_id',$this->department);
        }

       if($facility){
            $this->db->where('facility_id',$facility);
        }
       $query = $this
                ->db
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get('ihrisdata');
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }
   
    function searchStaff($limit,$start,$search,$col,$dir)
    {
        $facility=$this->facility;
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        if($facility){
            $this->db->where('facility_id',$facility);
        }
        
        $query = $this
                ->db
                ->like('surname',$search)
                ->or_like('othername',$search)
                ->or_like('firstname',$search)
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get('ihrisdata');
        
       
        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    public function countforSearch($search)
    {
        $facility=$this->facility;
        if($this->department){
            $this->db->where('department_id',$this->department);
        }
        if($facility){
            $this->db->where('facility_id',$facility);
        }
        
        $query = $this->db
                ->like('surname',$search)
                ->or_like('othername',$search)
                ->or_like('firstname',$search)
                ->get('ihrisdata');
    
        return $query->num_rows();
    } 
   
    public function count_timelogs(){
		$query=$this->db->get('time_log');
		return $query->num_rows();
		
	}
    public function getTimeLogs($limit=false,$start=FALSE,$search_data=FALSE){
        
        		
        $facility=$this->facility; //current facility
      
		
         $search_data=$this->input->post();
     
          if($search_data){
	      $date_from=$search_data['date_from'];
          $date_to=$search_data['date_to'];
          $date_from=date('Y-m-d', strtotime($date_from));
	      $date_to=date('Y-m-d', strtotime($date_to));
          $name=$search_data['name'];
          
          if($name){
              
              $ids=$this->getIds($name);
              
              if(count($ids)>0){
              
              $this->db->where_in('time_log.ipps',$ids);
              }
          }
          
          $this->db->where("date >= '$date_from' AND date <= '$date_to'");
          }
          $facility=$this->facility;

          $department=$this->department;
          $division=$this->division;
          $unit=$this->unit;
  
      
        if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){
  
  
                  if(!empty($department)){
                      $this->db->where('ihrisdata.department_id',$department);
                      
                  }
                 
  
                  if(!empty($division)){
                      $this->db->where('ihrisdata.division',$division);
                      
                  }
  
  
                  if(!empty($unit)){
                       $this->db->where('ihrisdata.unit',$unit);
                      
                  }
                  if(!empty($facility)){
                      $this->db->where('ihrisdata.facility_id',$facility);
                     
                 }
                
                  
                 
          
        }
      
         $this->db->limit($limit,$start);
         $facility=$this->facility;
        //  $this->db->query->order_by ('date', 'asc');
    
	 $this->db->join("ihrisdata","ihrisdata.ipps=time_log.ipps");
	    $query=$this->db->get("time_log");
	    return $query->result();
	}

	public function getIds($name){

	$facility=$this->facility; //current facility
	$this->db->select('ipps');
    $this->db->where("firstname like '%$name%'");
    $this->db->or_where("surname like '%$name' ");
    $query=$this->db->get('ihrisdata');
    $result=$query->result();
    $ids=array();
    foreach($result as $row){
        
        array_push($ids,$row->ipps);
    }
    
    return $ids;
    }

    public function count_Staff()
    {
        $facility=$this->facility;

        $department=$this->department;
        $division=$this->division;
        $unit=$this->unit;

    
      if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){


                if(!empty($department)){
                    $this->db->where('department_id',$department);
                    
                }
               

                if(!empty($division)){
                    $this->db->where('division',$division);
                    
                }


                if(!empty($unit)){
                     $this->db->where('unit',$unit);
                    
                }
                if(!empty($facility)){
                    $this->db->where('facility_id',$facility);
                   
               }
              
                
               
        
      }
        $query=$this->db->get('ihrisdata');

        return $query->num_rows();
        
    }


    public function get_printableTimeLogs(){

        //$this->db->join('ihrisdata', 'ihrisdata.ihris_pid = time_log.ihris_pid');

        $query = $this->db->get('time_log');
        return $query->result();
    }

   public function countTimesheet($valid_range){

    $facility=$this->facility;

    $department=$this->department;
    $division=$this->division;
    $unit=$this->unit;


  if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){


            if(!empty($department)){
                $this->db->where('department_id',$department);
                
            }
           

            if(!empty($division)){
                $this->db->where('ihrisdata.division',$division);
                
            }


            if(!empty($unit)){
                 $this->db->where('ihrisdata.unit',$unit);
                
            }
            if(!empty($facility)){
                $this->db->where('ihrisdata.facility_id',$facility);
               
           }
          
            
           
    
  }
    $this->db->like('time_sheet.date',$valid_range,'after');
    $this->db->join('ihrisdata','ihrisdata.ipps=time_sheet.ipps');
    $query=$this->db->get('time_sheet');

    return $query->num_rows();
    
}

     


    Public function fetch_TimeSheet($date_range=NULL,$start=NULL,$limit=NULL,$employee=NULL){    

        $facility=$this->session->userdata['facility'];
        $department=$this->department;
        $division=$this->division;
        $unit=$this->unit;

        $month=$this->input->post('month');
        $year=$this->input->post('year');
        
         $date=$year."-".$month;

        if($month!="")
        {

            $valid_range=$date;

        }

        else{

            $valid_range=$date_range;
        }
        if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){

            if(!empty($facility)){
                $this->db->where('ihrisdata.facility_id',$facility);
               
           }
            if(!empty($department)){
                $this->db->where('ihrisdata.department_id',$department);
                
            }
           

            if(!empty($division)){
                $this->db->where('ihrisdata.division',$division);
                
            }


            if(!empty($unit)){
                 $this->db->where('ihrisdata.unit',$unit);
                
            }        
    
  }

        $search="";

		if(!empty($employee)){
            $search="and hr.ihris_pid='".$employee."'";
			//$this->db->where('ihris_pid',$employee);
		 }

        $this->db->select();
        $this->db->distinct('ipps');
        $this->db->from('ihrisdata');
        $all=$this->db->get();

       

        $rows=$all->result_array();

        foreach($rows as $row){

            $id= $row['ipps'];

                $query=$this->db->query("select  
                t.date, t.ipps,
                max(t.day1) as day1,
                max(t.day2)as day2,
                max(t.day3)as day3,
                max(t.day4)as day4,
                max(t.day5)as day5,
                max(t.day6)as day6,
                max(t.day7)as day7,
                max(t.day8)as day8,
                max(t.day9)as day9,
                max(t.day10)as day10,
                max(t.day11)as day11,
                max(t.day12)as day12,
                max(t.day13)as day13,
                max(t.day14)as day14,
                max(t.day15)as day15,
                max(t.day16)as day16,
                max(t.day17)as day17,
                max(t.day18)as day18,
                max(t.day19)as day19,
                max(t.day20)as day20,
                max(t.day21)as day21,
                max(t.day22)as day22,
                max(t.day23)as day23,
                max(t.day24)as day24,
                max(t.day25)as day25,
                max(t.day26)as day26,
                max(t.day27)as day27,
                max(t.day28)as day28,
                max(t.day29)as day29,
                max(t.day30)as day30,
                max(t.day31)as day31,
                concat(hr.surname,' ',hr.firstname) as fullname from time_sheet t,
  ihrisdata hr where hr.ipps=t.ipps and t.ipps='$id' and  t.date like '$valid_range-%' $search LIMIT $limit, $start");

          

            $rowdata=$query->result_array();

            if(!empty($rowdata[0]['ipps'])){

            $data[]=$rowdata[0];
           }
          
    }


            return  $data;

}
public function count_employeeTimelogs($ipps){

    $this->db->where('ipps',$ipps);
    $query=$this->db->get('time_log');
    return $query->num_rows();
    
}

Public function getEmployeeTimeLogs($ipps,$limit=false,$start=FALSE,$search_data=FALSE,$search_data2=FALSE){
    
           
    if($search_data){
      $date_from=$search_data['date_from'];
      $date_to=$search_data['date_to'];
      $date_from=date('Y-m-d', strtotime($date_from));
	  $date_to=date('Y-m-d', strtotime($date_to));
  
      
      $this->db->where("date >= '$date_from' AND date <= '$date_to'");
      }
      if($search_data2){
        $date_from=$search_data2['date_from'];
        $date_to=$search_data2['date_to'];
        $date_from=date('Y-m-d', strtotime($date_from));
        $date_to=date('Y-m-d', strtotime($date_to));
    
        
        $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }

       $this->db->where("time_log.ipps",$ipps);
  
       $this->db->limit($limit,$start);

       $query=$this->db->get("time_log");
       $data['timelogs']=$query->result();

       //======userdata====

       $this->db->where('ipps',$ipps);
       $qry=$this->db->get('ihrisdata');
       $data['employee']=$qry->row();

       //leave info
       $ihris_pid=@$data['employee']->ihris_pid;
       $this->db->where('ihris_pid',$ihris_pid);
       $this->db->where('schedule_id','25');
        if($search_data){
       $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if($search_data2){
            $date_from=$search_data2['date_from'];
            $date_to=$search_data2['date_to'];
            $date_from=date('Y-m-d', strtotime($date_from));
            $date_to=date('Y-m-d', strtotime($date_to));
        
            
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
            }

       $qry=$this->db->get('actuals');

       $data['leaves']=$qry->result();

       //offs info
       $ihris_pid=@$data['employee']->ihris_pid;
       $this->db->where('ihris_pid',$ihris_pid);
       $schedules=array('24','27');
       $this->db->where_in('schedule_id',$schedules);
        if($search_data){
       $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if($search_data2){
            $date_from=$search_data2['date_from'];
            $date_to=$search_data2['date_to'];
            $date_from=date('Y-m-d', strtotime($date_from));
            $date_to=date('Y-m-d', strtotime($date_to));
        
            
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
            }
   
       $qry=$this->db->get('actuals');

       $data['offs']=$qry->result();

        //requests info
        $ihris_pid=@$data['employee']->ihris_pid;
        $this->db->where('ihris_pid',$ihris_pid);
        $this->db->where('schedule_id','23');
         if($search_data){
        $this->db->where("date >= '$date_from' AND date <= '$date_to'");
         }
         if($search_data2){
            $date_from=$search_data2['date_from'];
            $date_to=$search_data2['date_to'];
            $date_from=date('Y-m-d', strtotime($date_from));
            $date_to=date('Y-m-d', strtotime($date_to));
        
            
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
            }
    
        $qry=$this->db->get('actuals');
 
        $data['requests']=$qry->result();

        //holidays

        $darray=array();
        $this->db->select('holidaydate');
        $this->db->from('public_holiday');
        $query=$this->db->get();
        $dates=$query->result();
        foreach($dates as $date){
         $fdates=   $date->holidaydate;
         array_push($darray,$fdates);
            
        }


       

    
        //days supposed to work
        $ihris_pid=@$data['employee']->ihris_pid;
      
        $this->db->where('ihris_pid',$ihris_pid);
        $this->db->where('schedule_id','14');
       
    
         if($search_data){
        $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
         }
         if($search_data2){
            $date_from=$search_data2['date_from'];
            $date_to=$search_data2['date_to'];
            $date_from=date('Y-m-d', strtotime($date_from));
            $date_to=date('Y-m-d', strtotime($date_to));
        
            
            $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
            }
            $this->db->where_not_in('duty_date',$darray);
        $qry=$this->db->get('duty_rosta');
 
        $data['dutydays']=$qry->result();
       
       return $data;
}






}