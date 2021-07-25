<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;

class Biotimejobs extends MX_Controller {
   
	
	public  function __construct(){
		parent:: __construct();

        $this->username=Modules::run('svariables/getSettings')->biotime_username;
        $this->password=Modules::run('svariables/getSettings')->biotime_password;
        $this->load->model('biotimejobs_mdl');


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

        
        
    public function create($data){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Authorization' => "Token ".$this->get_general_auth(),
            'Authorization' =>"JWT ". $this->get_token(),
        ];
        
        $response = $http->sendRequest('iclock/api/terminals',"GET",$headers,[],$search=FALSE);

        print_r($response);
    }
    public function update($data){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Authorization' => "Token ".$this->get_general_auth(),
            'Authorization' =>"JWT ". $this->get_token(),
        ];
        
        $response = $http->sendRequest('iclock/api/terminals',"PUT",$headers,[],$search=FALSE);

        
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

   $this->log($message);

 }
    

//  $response = $this->getTime($spage="",$user_date);
//  $count = $response->count;
//  $pages = (int)ceil($count/10);
 //empty array to package terminal data
 // $rows=array();
 // for ($currentPage=1; $currentPage<=$pages; $currentPage++){
 //   $responses = $this->getTime($currentPage,$user_date);
 //   foreach($responses->data as $resp){
 //   $data  =array("emp_code" => $resp['emp_code'],
 //                 "terminal_sn" => $resp['terminal_sn'],
 //                 "area_alias" => $resp['area_alias'],
 //                 "longitude" => $resp['longitude'],
 //                 "latitude" => $resp['latitude'],
 //                 "punch_state" => $resp['punch_state'],
 //                 "punch_time" => $resp['punch_time']
 //  );

 //  array_push($rows,$data);
 
 //       }

 // }
 // print_r();


public function log($message){
    //add double [] at the beggining and at the end of file contents
   return file_put_contents('log.txt', "\n{".'"REQUEST DETAILS: '.date('Y-m-d H:i:s').' Time": '.json_encode($message).'},',FILE_APPEND);
}

public function logAttendance($message){
    //add double [] at the beggining and at the end of file contents
    return file_put_contents('log.txt', "\n{".'"REQUEST DETAILS: '.date('Y-m-d H:i:s').' Time: "'.json_encode($message).'},',FILE_APPEND);
 }





}