<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;

class Biotimejobs extends MX_Controller {
   
	
	public  function __construct(){
		parent:: __construct();

        $this->username=Modules::run('svariables/getSettings')->biotime_username;
        $this->password=Modules::run('svariables/getSettings')->biotime_password;
        $this->load->model('biotimejobs_mdl');
        $this->facility=$_SESSION['facility'];


    }

	public function index()
	{
     echo "BIO-TIME HERE";

	}
   
    public function get_token($uri=FALSE){

            $http = new HttpUtil();
            $headers = ['Content-Type' => 'application/json'];
            $body = array(
                "username"=>$this->username,
                "password"=>$this->password
            );
            $response = $http->sendRequest('jwt-api-token-auth',"POST",$headers,$body,$search=FALSE);
    // print_r ($response->token);
     return $response->token;
    
   
    }
//get terminals
    public function terminals(){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Authorization' => "Token ".$this->get_general_auth(),
            'Authorization' =>"JWT ". $this->get_token(),
        ];
      
       $response = $http->sendRequest('iclock/api/terminals',"GET",$headers,[]);

  
    return $response;

    }
    //cron job
    //Fetches ihris stafflsit via the api
    public function get_ihrisdata(){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        
        $response = $http->sendiHRISRequest('mohattendance_dev/apiv1/api/ihrisdata',"GET",$headers,[]);

        if($response){
         $message= $this->biotimejobs_mdl->add_ihrisdata($response);
         $this->log($message);
        }
       

    }
   //employees all enrolled users before creating new ones.
   
   public function get_Enrolled($page=FALSE)
   {

      $http = new HttpUtil();
      $headers = [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'Authorization' =>"JWT ".$this->get_token(),
      ];
 
     // $endpoint='iclock/api/transactions/';
      $endpoint='personnel/api/employees/';
      $options = (object) array(
       
          "page"=>$page
      );
   

       $response = $http->get_List($endpoint,"GET",$headers,$options);
    return $response;
  }
  //cronjob
  //get enrolled data from biotime
  //after nun  call fingerprint cache procedure
     public function saveEnrolled(){
        $resp=$this->get_Enrolled();
        $count = $resp->count;
        $pages = (int)ceil($count/10);
        $rows=array();
    
        for ($currentPage=1; $currentPage<=$pages; $currentPage++)
        {
            $response = $this->get_Enrolled($currentPage);
            foreach($response->data as $mydata){
            
                           $data = array(
                            'entry_id' =>$mydata->area[0]->area_code.'-'.$mydata->emp_code,  
                            "card_number" => $mydata->emp_code,
                            'facilityId'=>$mydata->area[0]->area_code,
                            'source'=>'Biotime',
                            'device'=>$mydata->enroll_sn,
                            'att_status'=> $mydata->enable_att);
                
                array_push($rows,$data);
            
             
        }
       }
      
       $message=$this->biotimejobs_mdl->add_enrolled($rows);

     $this->log($message);
    }
//get cron jobs from the server
    public function getTime($page=FALSE,$userdate=FALSE)
    {

       $http = new HttpUtil();
       $headers = [
           'Content-Type' => 'application/json',
           'Accept' => 'application/json',
           'Authorization' =>"JWT ".$this->get_token(),
       ];
       if(empty($userdate)){
        $edate=date('Y-m-d H:i:s');
        }
        else{
            $page=$userdate;
        }
     
       //if las sync is empty
      
       $sdate=date("Y-m-d H:i:s",strtotime("-24 hours"));
       $query=array('page'=>$page,'start_time'=>$sdate,
       'end_time'=>$edate,
       );
      
       $params='?'.http_build_query($query);
       $endpoint='iclock/api/transactions/'.$params;
    
       //leave options and undefined. guzzle will use the http:query;
       
       $response = $http->getTimeLogs($endpoint,"GET",$headers);
        //return $response;
    return $response;
   }


   public function fetchBiotTimeLogs($user_date=FALSE){
    $resp=$this->getTime($page=1,$user_date);
    $count = $resp->count;
    $pages = (int)ceil($count/10);
    $rows=array();

    for ($currentPage=1; $currentPage<=$pages; $currentPage++)
    {
        $response = $this->getTime($currentPage,$user_date);
        foreach($response->data as $mydata){

            $data=array("emp_code" => $mydata ->emp_code,
                            "terminal_sn" => $mydata->terminal_sn,
                            "area_alias" => $mydata->area_alias,
                            "longitude" => $mydata->longitude,
                            "latitude" => $mydata->latitude,
                            "punch_state" => $mydata->punch_state,
                            "punch_time" => $mydata->punch_time
             );
            array_push($rows,$data);
            
    }
   }
 
  
   $message=$this->biotimejobs_mdl->add_time_logs($rows);

   $this->logattendance($message);

 }


 //enroll new users (Front End Action that requires login);
 public function get_new_users($facility){
    $query=$this->db->query("SELECT * FROM  ihrisdata WHERE ihrisdata.facility_id='$facility' AND ihrisdata.card_number NOT IN (SELECT fingerprints_staging.card_number from fingerprints_staging)");
 return $query->result();
 }
 // create new user
 
 public function create_new_biotimeuser($firstname,$surname,$emp_code,$area,$department,$position){
    $farea=urldecode($area);
    $fjob=urldecode($position);
    $fdep=urldecode($department);

    $barea=$this->getbioloc($farea);
    if(empty($barea)){
        $parea=1;
    }
    else{
        $parea=$barea;
    }
    $bjob=$this->getbiojobs($fjob);
    if(empty($bjob)){
        $pjobs=1;
    }
    else{
        $pjobs=$bjob;
    }
    $bdep=$this->getbiodeps($fdep);
    if(empty($bdep)){
        $pdep=1;
    }
    else{
        $pdep=$bdep;
    }

    $http = new HttpUtil();
   
    $body = array(
        'first_name' => $firstname,
        'last_name' => $surname,
        'emp_code' => $emp_code,
        'area' => [(string)$parea], 
        'department' => (string)$pdep, 
        'position' => (string)$pjobs,	
		);

    $endpoint='personnel/api/employees/';
    $headr = array();
    $headr[] = 'Content-length:'.strlen(json_encode($body));
    $headr[] = 'Content-type: application/json';
    $headr[] = 'Authorization: JWT '.$this->get_token();
    
    $response = $http->curlsendHttpPost($endpoint,$headr,$body);

     if($response){
    $this->log($response);
    }
   

}
public function log($message){
    //add double [] at the beggining and at the end of file contents
   return file_put_contents('log.txt', "\n{".'"REQUEST DETAILS: '.date('Y-m-d H:i:s').' Time": '.json_encode($message).'},',FILE_APPEND);
}
public function logattendance($message){
    //add double [] at the beggining and at the end of file contents
   return file_put_contents('fetchatt_log.txt', "\n{".'"REQUEST DETAILS: '.date('Y-m-d H:i:s').' Time": '.json_encode($message).'},',FILE_APPEND);
}
public function getbiojobs($job){
 $query=$this->db->query("SELECT id from biotime_jobs where position_code='$job' LIMIT 1");
 return $query->result()[0]->id;

}
public function getbiodeps($dep_id){
    $query=$this->db->query("SELECT id from biotime_departments where dept_code='$dep_id' LIMIT 1");
    return $query->result()[0]->id;

}
public function getbioloc($facility){
    $query=$this->db->query("SELECT id from biotime_facilities where area_code='$facility' LIMIT 1");
    return $query->result()[0]->id;

}
//not working
public function biotimeFacilities()
    {

       $http = new HttpUtil();
       $headr = array();
       $headr[] = 'Content-length: 0';
       $headr[] = 'Content-type: application/json';
       $headr[] = 'Authorization: JWT '.$this->get_token();
    

 
       $query=array('page_size'=>50000
       );
      
       $params='?'.http_build_query($query);
       $endpoint='personnel/api/areas/'.$params;
    
       //leave options and undefined. guzzle will use the http:query;
       
       $response = $http->curlgetHttp($endpoint,$headr,[]);
        //return $response;
         //return $response;
         $j=array();
         foreach ($response->data as $facs) {
             $data=array('id'=>$facs->id,
                         'area_code'=>$facs->area_code,
                         'area_name'=>$facs->area_name
         );
         array_push($j,$data);
             
         }
         
         $message=$this->biotimejobs_mdl->save_facilities($j);
        //  print_r($response->data[0]->id);
         return $this->log($message);
   }
   public function biotime_jobs()
    {

       $http = new HttpUtil();
       $headr = array();
       $headr[] = 'Content-length: 0';
       $headr[] = 'Content-type: application/json';
       $headr[] = 'Authorization: JWT '.$this->get_token();
    

 
       $query=array('page_size'=>50000
       );
      
       $params='?'.http_build_query($query);
       $endpoint='personnel/api/position/'.$params;
    
       //leave options and undefined. guzzle will use the http:query;
       
       $response = $http->curlgetHttp($endpoint,$headr,[]);
          //return $response;
          $j=array();
          foreach ($response->data as $jobs) {
              $data=array('id'=>$jobs->id,
                          'position_code'=>$jobs->position_code,
                          'position_name'=>$jobs->posistion_name
          );
          array_push($j,$data);
              
          }
          
          $message=$this->biotimejobs_mdl->save_jobs($j);
    return $this->log($message);
   }
   public function biotimedepartments()
    {

       $http = new HttpUtil();
       $headr = array();
       $headr[] = 'Content-length: 0';
       $headr[] = 'Content-type: application/json';
       $headr[] = 'Authorization: JWT '.$this->get_token();
    

 
       $query=array('page_size'=>5000000
       );
      
       $params='?'.http_build_query($query);
       $endpoint='personnel/api/department/'.$params;
    
       //leave options and undefined. guzzle will use the http:query;
       
       $response = $http->curlgetHttp($endpoint,$headr,[]);
        //return $response;
    $j=array();
    foreach ($response->data as $deps) {
        $data=array('id'=>$deps->id,
                    'dep_code'=>$deps->dept_code,
                    'dept_name'=>$deps->dept_name
    );
    array_push($j,$data);
        
    }
    
    $message=$this->biotimejobs_mdl->save_department($j);
    return $this->log($message);
   
   }








}