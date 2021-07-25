<?php namespace utils;

use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Promise\Promise;
use \Psr\Http\Message\ResponseInterface;

class HttpUtil{

    public  function __construct(){

        $this->CI = & get_instance();
        $this->client = new Client(['base_uri' => BIO_URL]);
        $this->ihrisclient = new Client(['base_uri' => iHRIS_URL]);
    }

    public function sendRequest($endpoint = "",$method="",$headers = [],$body = []){
        $request = new Request($method,$endpoint."/", $headers,json_encode($body));
        $response =  $this->client->send($request);
        return json_decode( (string) $response->getBody()->getContents());
    }

    public function sendiHRISRequest($endpoint = "",$method="",$headers = [],$body = []){
       
        $request = new Request($method,$endpoint."/", $headers,json_encode($body));
        $response =  $this->ihrisclient->send($request);
        return json_decode( (string) $response->getBody()->getContents());
        //$result = json_decode($response->getBody()->getContents());
    }
   
    public function getData($endpoint, $method="",$headers = [],$options)
    {
      
       $url=BIO_URL.$endpoint;

    

        //do{
            $response = $this->client->request(
                $method,
                $url,
                [
                    'headers' => $headers,
                    'query' => [
                        "page" => $options->page,
                        "start_time"=>$options->start_time,
                        "end_time"=>$options->end_time,
                        "terminal_sn"=>$options->terminal_sn,
                    ]
                ]
            );
            
    return json_decode( (string) $response->getBody()->getContents());
    }
    public function getTimeLogs($endpoint, $method="",$headers = [])
    {
        // die(var_dump($this->get_jwt_auth()));
      
       $url=BIO_URL.$endpoint;

        //do{
            $response = $this->client->request(
                $method,
                $url,
                [
                    'headers' => $headers
                    
                ]
            );
            
       
        return json_decode( (string) $response->getBody()->getContents());
    }
    public function get_List($endpoint, $method="",$headers = [],$options)
    {
        // die(var_dump($this->get_jwt_auth()));
      
       $url=BIO_URL.$endpoint;

        //do{
            $response = $this->client->request(
                $method,
                $url,
                [
                    'headers' => $headers,
                    'query' => [
                        
                        "page" => $options->page
                        ]
                ]
            );
            
       
        return json_decode( (string) $response->getBody()->getContents());
    }
   

    public function sendAsync($endpoint = "",$method="",$header = [],$body = []){
        $headers  = ['Content-Type' => 'application/json'];
        $headers  = array_merge($headers,$header);
       // Send an asynchronous request.
        $request  = new Request($method,$endpoint, $headers,json_encode($body));
        $promise = $this->client->sendAsync($request)->then(function ($response) {
            echo 'I completed! ' . $response->getBody();
        });
        $promise->wait();
    return $promise;

    }
    //http requests

 public function sendHttpPost($url,$headers=[],$body){
 
    $ch = curl_init($url);

    file_put_contents('log.txt', "\n HEADERS OUT ".json_encode($headers),FILE_APPEND);
     //post values
    curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
    // Option to Return the Result, rather than just true/false
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Set Request Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //time to wait while waiting for connection...indefinite
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    curl_setopt($ch,CURLOPT_POST,1);
    //set curl time..processing time out
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    // Perform the request, and save content to $result
    ini_set("max_execution_time",400);
    $result = curl_exec($ch);
      //curl error handling
      $curl_errno = curl_errno($ch);
              $curl_error = curl_error($ch);
              if ($curl_errno > 0) {
                     curl_close($ch);
                    return  "CURL Error ($curl_errno): $curl_error\n";
                  }
        $info = curl_getinfo($ch);
       file_put_contents('log.txt', "\n REQUEST FULL ".json_encode($info),FILE_APPEND);
       curl_close($ch);
       $decodedResponse =json_decode($result);
       return $decodedResponse;
}

public function sendHttpGet($url,$headers,$body=[]){

    $ch = curl_init($url);

    file_put_contents("logs.txt", "\n HEADERS OUT ".json_encode($headers),FILE_APPEND);
     //post values
    //curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
    // Option to Return the Result, rather than just true/false
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Set Request Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //time to wait while waiting for connection...indefinite
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    curl_setopt($ch,CURLOPT_POST,0);
    //set curl time..processing time out
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    // Perform the request, and save content to $result
    ini_set("max_execution_time",400);
    $result = curl_exec($ch);
      //curl error handling
      $curl_errno = curl_errno($ch);
              $curl_error = curl_error($ch);
              if ($curl_errno > 0) {
                     curl_close($ch);
                    return  "CURL Error ($curl_errno): $curl_error\n";
                  }

        $info = curl_getinfo($ch);
       file_put_contents('log.tx', "\n REQUEST FULL ".json_encode($info),FILE_APPEND);
       curl_close($ch);
       $decodedResponse =json_decode($result);
       return $decodedResponse;
}

    

}



?>