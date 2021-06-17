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
    }

    public function sendRequest($endpoint = "",$method="",$headers = [],$body = []){
       
        $request = new Request($method,$endpoint."/", $headers,json_encode($body));
        $response =  $this->client->send($request);
        return json_decode( (string) $response->getBody()->getContents());
        //$result = json_decode($response->getBody()->getContents());
    }
  
    public function getData($endpoint, $method="",$headers = [],$options)
    {
        // die(var_dump($this->get_jwt_auth()));
      
       $url=BIO_URL.$endpoint;

        $after = 0;
        $result = [];
        $maxValue = null;

        //do{
            $response = $this->client->request(
                $method,
                $url,
                [
                    'headers' => $headers,
                    'query' => [
                        'start_time' => $options->start,
                        'end_time'   => $options->end,
                        'terminal_sn' =>$options->terminal,
                        "page" => $options->page
                        ]
                ]
            );
            
            $result = (string) $response->getBody()->getContents();
        return (object) json_decode($result,true);
    }
    public function sendAsync($endpoint = "",$method="",$header = [],$body = []){
        $headers  = ['Content-Type' => 'application/json'];
        $headers  = array_merge($headers,$header);
        $request  = new Request($method,$endpoint, $headers,json_encode($body));
        $promise =  $this->client->sendAsync($request);
        
        $promise->then(function(Response $res){
            file_put_contents("logs.txt", json_encode($res->getBody()));
        },function($res){
            file_put_contents("logs.txt", json_encode($res));
        });
        //$promise->wait();
        //json_decode( (string) $response->getBody());
    }

    

}



?>