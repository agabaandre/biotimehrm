<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;

class Biotimejobs extends MX_Controller {
   
	
	public  function __construct(){
		parent:: __construct();

        $this->username=Modules::run('svariables/getSettings')->biotime_username;
        $this->password=Modules::run('svariables/getSettings')->biotime_password;


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
            $response = $http->sendRequest('jwt-api-token-auth',"POST",$headers,$body);
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
//employees
    public function get_Enrolled(){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' =>"JWT ". $this->get_token(),
        ];
        
        $response = $http->sendRequest('iclock/api/terminals',"GET",$headers,[]);

        print_r($response);
    }
    public function create($data){
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Authorization' => "Token ".$this->get_general_auth(),
            'Authorization' =>"JWT ". $this->get_token(),
        ];
        
        $response = $http->sendRequest('iclock/api/terminals',"GET",$headers,[]);

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
        
        $response = $http->sendRequest('iclock/api/terminals',"PUT",$headers,[]);

        
    }

    public function getTime($page=FALSE,$machine=FALSE,$sdate=FALSE,$edate=FALSE,$limit=FALSE)
    {

       $http = new HttpUtil();
       $headers = [
           'Content-Type' => 'application/json',
           'Accept' => 'application/json',
           'Authorization' =>"JWT ".$this->get_token(),
       ];
       $startdate=$sdate;
       $enddate=$edate;
       //    $startdate="2021-06-13";
       //    $enddate="2021-06-14";
       $terminal=$machine; 
       $endpoint='iclock/api/transactions/';
       
       $options = (object) array(
           "start"=>$startdate,
           "end"=>$enddate,
           "terminal"=>$terminal,
           "page"=>$page
       );
    

        $response = $http->getData($endpoint,"GET",$headers,$options);
        return $response;
        //print_r($response);
   }


   public function getLogging($machine){
   $response = $this->getTime($page=NULL,$machine);
   $count = $response->count;
   $pages = (int)ceil($count/10);
   $rows=array();
   $terminal=$machine;
   for ($currentPage=1; $currentPage<=$pages; $currentPage++){
      $responses = $this->getTime($currentPage,$terminal);
      foreach($responses->data as $resp){
      $data  =array("emp_code" => $resp['emp_code'],
                    "terminal_sn" => $resp['terminal_sn'],
                    "area_alias" => $resp['area_alias'],
                    "longitude" => $resp['longitude'],
                    "latitude" => $resp['latitude'],
                    "punch_state" => $resp['punch_state'],
                    "punch_time" => $resp['punch_time']
     );

     array_push($rows,$data);
    }

    }
    //sync Logging
//   $total_records=count($rows);
//   $sdate=
//   $edate=
//   $machine;
//   $facility=$resp['area_alias'];

//   $log=array(	
//                "start_date"
//                "end_date"	
//                 "machine"=
//                 "facility" =>	$resp['area_alias']
//                 "records" => count($rows)
//   )
  
 
  
  if(count($rows)>0):
   $this->db->insert_batch('biotime_data',$rows);
  endif;
    
}


public function autoTerminaldata(){
    set_time_limit(5000);
    $terminals=$this->terminals();

    foreach ($terminals->data as $serial):
        $terminal=$serial->sn;
        $this->getLogging($terminal);
    endforeach;


}

public function manTerminaldata($terminal){
  
        $this->getLogging($terminal);
   

}






}