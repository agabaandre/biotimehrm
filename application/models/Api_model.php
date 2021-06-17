<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 
class Api_model extends CI_Model {
    
       public function registration_checkUser($email){
            return $this->db->select("*")
            ->from('user')
            ->where('email', $email)
            ->get();
    }
    
    //user registration part
    public function insert_user($data = array())
    {
        return $this->db->insert('user', $data);
    }
    
    
        // Auth check user
    public function checkUser($data = array()){
            return $this->db->select("*")
            ->from('user')
            ->where('email', $data['email'])
            ->where('password', md5($data['password']))
            ->get();
    }
    
    public function userdata($email){
         return $this->db->select("a.*,b.department_name,c.image as profile_pic")
            ->from('employee_history a')
            ->join('department b','b.dept_id = a.dept_id','left')
            ->join('user c','c.email = a.email','left')
            ->where('a.email', $email)
            ->get()
            ->row();
    }
    
    
        public function password_recovery($data = array())
    {
        return $this->db->select("*")
            ->from('user')
            ->where('email',$data['email'])
            ->get();
    }
        public function update_recovery_pass($data = [])
    {
        return $this->db->where('email',$data['email'])
            ->update('user',$data); 
    } 
    public function token_matching($token_id){
        return $this->db->select("*")
            ->from('user')
            ->where('password_reset_token',$token_id)
            ->get(); 
    }
    
    public function atten_create($data = array()){
        
    return $this->db->insert('attendance_history', $data);
        
    }
    
    public function attendance_history($employee_id){
         return $this->db->select("*,DATE(time) as mydate,timediff(MAX(time),MIN(time)) as totalhours")
            ->from('attendance_history')
            ->where('uid',$employee_id)
            ->group_by('mydate')
            ->order_by('time','desc')
            ->get()
            ->result_array();
    }
    
      public function count_att_history($employee_id){
       return $this->db->select("*,DATE(time) as mydate,timediff(MAX(time),MIN(time)) as totalhours")
            ->from('attendance_history')
            ->where('uid',$employee_id)
            ->group_by('mydate')
            ->order_by('time','desc')
            ->get()
            ->num_rows();
    }
    
    public function attendance_historylimit($employee_id,$limit){
         return $this->db->select("*,DATE(time) as mydate,timediff(MAX(time),MIN(time)) as totalhours")
            ->from('attendance_history')
            ->where('uid',$employee_id)
            ->group_by('mydate')
            ->order_by('time','desc')
            ->limit($limit)
            ->get()
            ->result_array();
    }
    
    public function attendance_history_datewise($id,$from_date,$to_date){
         $att = "SELECT *, DATE(time) as mydate FROM `attendance_history` WHERE `uid`=$id AND DATE(time) BETWEEN '" . $from_date . "' AND  '" . $to_date . "' GROUP BY mydate ORDER BY time desc";
    $query = $this->db->query($att)->result();
    $attendance = [];
    $i=1;
    foreach ($query as $att) {
         $attendance[] = $this->db->select('MIN(a.time) as intime,MAX(a.time) as outtime,a.uid,a.time,timediff(MAX(a.time),MIN(a.time)) as totalhours,DATE(time) as date,Time(time) as punchtime')
->from('attendance_history a')
->like('a.time',date( "Y-m-d", strtotime($att->mydate)),'after')
->where('a.uid',$att->uid)
->order_by('a.time','DESC')
->get()
->result_array();



$i = 1;
    foreach($attendance as $k => $v){
        
         $attendance[$k]['totalhours'] = $attendance[$k][0]['totalhours'];
           $attendance[$k]['date']       = $attendance[$k][0]['date'];
        $data = $this->db->select('a.*,b.first_name,b.last_name')
->from('attendance_history a')
->join('employee_history b','a.uid = b.employee_id','left')
->like('a.time',date( "Y-m-d", strtotime($attendance[$k][0]['time'])),'after')
->where('a.uid',$attendance[$k][0]['uid'])
->order_by('a.atten_his_id','ASC')
->get()
->result();



 $ix=1;
             $in_data = [];
             $out_data = [];
           foreach ($data as $attendancedata) {

            if($ix % 2){
       $status = "IN";
       $in_data[$ix] = $attendancedata->time;

    }else{
        $status = "OUT";
        $out_data[$ix] = $attendancedata->time;
    }
     $ix++;
}

 $result_in = array_values($in_data);
        $result_out = array_values($out_data);
        $total = [];
        $count_out = count($result_out);
        if($count_out == 2){
        $n_out = $count_out;
        }else{
         $n_out = $count_out-1;   
        }
        for($i=0;$i < $n_out; $i++) {

                $date_a = new DateTime($result_in[$i+1]);
                $date_b = new DateTime($result_out[$i]);
                $interval = date_diff($date_a,$date_b);

            $total[$i] =  $interval->format('%h:%i:%s');
        }
     $hou = 0;
     $min = 0;
     $sec = 0;
     $totaltime = '00:00:00';
    $length = sizeof($total);

            for($x=0; $x <= $length; $x++){
                    $split = explode(":", @$total[$x]); 
                    $hou += @$split[0];
                    $min += @$split[1];
                    $sec += @$split[2];
            }
            $seconds = $sec % 60;
            $minutes = $sec / 60;
            $minutes = (integer)$minutes;
            $minutes += $min;
            $hours = $minutes / 60;
            $minutes = $minutes % 60;
            $hours = (integer)$hours;
            $hours += $hou % 24;
           $attendance[$k]['wastage'] =   $totalwastage = $hours.":".$minutes.":".$seconds;
           
                $totalhours = new DateTime($attendance[$k][0]['totalhours']);
                $wastagehours = new DateTime($totalwastage);
                $networkhours = date_diff($totalhours,$wastagehours);

            $attendance[$k]['nethours'] = $networkhours->format('%h:%i:%s');
              

    }
    



$i++;
    }
    return $attendance;
        
    }
    
    
    public function notice_board($start){
         return $this->db->select("*")
            ->from('notice_board')
            ->limit($start)
            ->order_by('notice_id','desc')
            ->get()
            ->result_array();
        
    }
    
     public function notice_boardall(){
         return $this->db->select("*")
            ->from('notice_board')
            ->order_by('notice_id','desc')
            ->get()
            ->result_array();
        
    }
    
      public function count_notice(){
         $this->db->select('*');
        $this->db->from('notice_board');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->num_rows();  
        }
        return 0;
    }
    
    
     public function attendance_totalday_currentmonth($id,$from_date,$to_date){
         $att = "SELECT *, DATE(time) as mydate FROM `attendance_history` WHERE `uid`=$id AND DATE(time) BETWEEN '" . $from_date . "' AND  '" . $to_date . "' GROUP BY mydate ORDER BY time desc";
    return $query = $this->db->query($att)->num_rows();
     }
     
     
     
     
 public function total_loan_amount($id){
         $loanreceive = "SELECT *, sum(repayment_amount) as totalreceive FROM `grand_loan` WHERE `employee_id`=$id";
     $totalpayble = $this->db->query($loanreceive)->row();
    
    $loanpaid = "SELECT *, sum(payment) as totalpaid FROM `loan_installment` WHERE `employee_id`=$id";
     $totalpaid = $this->db->query($loanpaid)->row();
     
     $due = (!empty($totalpayble)?$totalpayble->totalreceive:0) - (!empty($totalpaid)?$totalpaid->totalpaid:0);
     return $due;
     }
     
     
      public function leave_remaining($id){
         $totalleave = "SELECT *, sum(leave_days) as totalleave FROM `leave_type`";
     $totalleave = $this->db->query($totalleave)->row();
    
    $takenleave = "SELECT *, sum(num_aprv_day) as takenlv FROM `leave_apply` WHERE `employee_id`=$id";
     $totaltaken = $this->db->query($takenleave)->row();
     
     $remainingleave = (!empty($totalleave)?$totalleave->totalleave:0) - (!empty($totaltaken)?$totaltaken->takenlv:0);
     return $remainingleave;
     }
     
     
     
      public function takenleave($id){
    $takenleave = "SELECT *, sum(num_aprv_day) as takenlv FROM `leave_apply` WHERE `employee_id`=$id";
     $totaltaken = $this->db->query($takenleave)->row();
     
     $tknlv = (!empty($totaltaken)?$totaltaken->takenlv:0);
     return $tknlv;
     }
    
  public function weekends(){
   $query_date = date('Y-m-d');
   $employee_id = $this->input->get('employee_id');
   $fromdate = date('Y-m-01', strtotime($query_date));
   $todate = date('Y-m-d');  
        
       $wknd = $this->db->select('*')->from('weekly_holiday')->get()->row();
       $holidays = $wknd->dayname;
       
        $weeklyholiday = array();
        $weeklyholiday = array_map('trim', explode(',', $holidays));
        $existdata = 0;
        
         if (sizeof($weeklyholiday) > 0) {
             foreach($weeklyholiday as $days){
                 
                  
         $begin = new DateTime($fromdate);
         $end = new DateTime($todate.' +1 day');

        $daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);

        foreach($daterange as $date){
            $dates = $date->format("l");
            if($days == $dates){
                $existdata += 1;
            }else{
               $existdata += 0; 
            }
            
        }
                 
             }
        }
        
        return $existdata;
        
        
    }
    
    public function totaldayofcurrentstage(){
    $query_date = date('Y-m-d');
   $employee_id = $this->input->get('employee_id');
   $fromdate = date('Y-m-01', strtotime($query_date));
   $todate = date('Y-m-d'); 
   
         $begin = new DateTime($fromdate);
         $end = new DateTime($todate.' +1 day');
         $daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);
         $result = 0;
            foreach($daterange as $date){
            $result +=1;
            
        }
        return $result;
    }
    
    public function salaryinfo($id){
     $data  = $this->db->select("a.*,CONCAT_WS(' ', b.first_name, b.last_name) AS employee_name,b.rate as basic,b.rate_type as salarytype")
                       ->from('employee_salary_payment a')->join('employee_history b','b.employee_id = a.employee_id','left')
                       ->where('a.employee_id',$id)
                       ->get()
                       ->result_array();   
     
     //print_r($data);exit();
     
     return $data;
        
    }
    
        public function count_salaryinfo($id){
         $this->db->select('*');
        $this->db->from('employee_salary_payment');
        $this->db->where('employee_id',$id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->num_rows();  
        }
        return 0;
    }
    
    
    
        public function salaryinfolimit($id,$limit){
     $data  = $this->db->select("a.*,CONCAT_WS(' ', b.first_name, b.last_name) AS employee_name,b.rate as basic,b.rate_type as salarytype")
                      ->from('employee_salary_payment a')->join('employee_history b','b.employee_id = a.employee_id','left')
                      ->where('a.employee_id',$id)
                      ->limit($limit)
                      ->get()
                      ->result_array();   
     return $data;
        
    }
    
    
            public function salary_addition_fields($id)
    {
        return $result = $this->db->select('employee_salary_setup.amount as percentage,salary_type.sal_name as salary_type')    
             ->from('employee_salary_setup')
             ->join('salary_type','salary_type.salary_type_id=employee_salary_setup.salary_type_id')
             ->where('employee_salary_setup.employee_id',$id)
             ->where('emp_sal_type',1)
             ->get()
             ->result();
    }

         public function salary_deduction_fields($id)
    {
        return $result = $this->db->select('employee_salary_setup.amount as percentage,salary_type.sal_name as salary_type')    
             ->from('employee_salary_setup')
             ->join('salary_type','salary_type.salary_type_id=employee_salary_setup.salary_type_id')
             ->where('employee_salary_setup.employee_id',$id)
             ->where('emp_sal_type',0)
             ->get()
             ->result();
    }
    
    public function type_list(){
      
         return $this->db->select("*")
            ->from('leave_type')
            ->get()
            ->result_array();
    }
    
    
 public function insert_leave_application($data = array())
    {
        return $this->db->insert('leave_apply', $data);
    }
    
    public function leave_list($employee_id){
         return $this->db->select("apply_strt_date as fromdate, apply_end_date as todate,apply_day,reason,status")
            ->from('leave_apply')
            ->where('employee_id',$employee_id)
            ->order_by('leave_appl_id','desc')
            ->get()
            ->result_array();
    }
    
       public function count_leave($employee_id){
         $this->db->select('*');
        $this->db->from('leave_apply');
         $this->db->where('employee_id',$employee_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->num_rows();  
        }
        return false;
    }
           
    
    
    public function leave_listlimit($employee_id,$limit){
         return $this->db->select("apply_strt_date as fromdate, apply_end_date as todate,apply_day,reason,status")
            ->from('leave_apply')
            ->where('employee_id',$employee_id)
            ->limit($limit)
            ->order_by('leave_appl_id','desc')
            ->get()
            ->result_array();
    }
    
    public function ledger($employee_id){
        $empinfo = $this->db->select('emp_his_id,first_name,last_name')->from('employee_history')->where('employee_id',$employee_id)->get()->row();
            $c_code = $employee_id;
            $c_name = $empinfo->first_name.$empinfo->last_name;
            $c_acc=$c_code.'-'.$c_name;
        
          $headcode =  $this->db->select('*')->from('acc_coa')->where('HeadName',$c_acc)->get()->row(); 
          
          
          $transaction = $this->db->select('VDate,Narration,Debit,Credit')->from('acc_transaction')->where('COAID',$headcode->HeadCode)->get()->result_array();
          
          return $transaction;
            
        
    }
    
    public function ledgerlimit($employee_id,$limit){
         $empinfo = $this->db->select('emp_his_id,first_name,last_name')->from('employee_history')->where('employee_id',$employee_id)->get()->row();
            $c_code = $employee_id;
            $c_name = $empinfo->first_name.$empinfo->last_name;
            $c_acc=$c_code.'-'.$c_name;
        
          $headcode =  $this->db->select('*')->from('acc_coa')->where('HeadName',$c_acc)->get()->row(); 
          
          
          $transaction = $this->db->select('VDate,Narration,Debit,Credit')->from('acc_transaction')->where('COAID',$headcode->HeadCode)->limit($limit)->get()->result_array();
          
          return $transaction;
    }
    
    public function count_ledger($employee_id){
            $empinfo = $this->db->select('emp_his_id,first_name,last_name')->from('employee_history')->where('employee_id',$employee_id)->get()->row();
            $c_code = $employee_id;
            $c_name = $empinfo->first_name.$empinfo->last_name;
            $c_acc=$c_code.'-'.$c_name;
        
          $headcode =  $this->db->select('*')->from('acc_coa')->where('HeadName',$c_acc)->get()->row();     
             
        
        
     return $transaction = $this->db->select('VDate,Narration,Debit,Credit')->from('acc_transaction')->where('COAID',$headcode->HeadCode)->limit($limit)->get()->num_rows();    
        
    }
    

    //KPI bar graph trends
    
}