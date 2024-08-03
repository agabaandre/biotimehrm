<?php
namespace utils;

use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Promise\Promise;
use \Psr\Http\Message\ResponseInterface;

class HttpUtils
{
    private $CI;
    private $client;
    private $ihrisclient;
     private $ihris5client;
    private $ucmbihrisclient;

    public function __construct()
    {

        $this->CI = &get_instance();
        $this->client = new Client(['base_uri' => BIO_URL]);
        $this->ihrisclient = new Client(['base_uri' => iHRIS_URL]);
        $this->ihris5client = new Client(['base_uri' => iHRIS5_URL]);
        $this->ucmbihrisclient = new Client(['base_uri' => UCMBiHRIS_URL]);
    }

    public function sendRequest($endpoint = "", $method = "", $headers = [], $body = [])
    {
        $request = new Request($method, $endpoint . "/", $headers, json_encode($body));
        $response = $this->client->send($request);
        return json_decode((string) $response->getBody()->getContents());
    }

    public function sendiHRISRequest($endpoint = "", $method = "", $headers = [], $body = [])
    {

        $request = new Request($method, $endpoint . "/", $headers, json_encode($body));
        $response = $this->ihrisclient->send($request);
        return json_decode((string) $response->getBody()->getContents());
        //$result = json_decode($response->getBody()->getContents());
    }
    public function sendiHRIS5Request($endpoint = "", $method = "", $headers = [], $body = [])
    {

        $request = new Request($method, $endpoint . "/", $headers, json_encode($body));
        $response = $this->ihris5client->send($request);
        return json_decode((string) $response->getBody()->getContents());
        //$result = json_decode($response->getBody()->getContents());
    }
    public function sendUCMBiHRISRequest($endpoint = "", $method = "", $headers = [], $body = [])
    {

        $request = new Request($method, $endpoint . "/", $headers, json_encode($body));
        $response = $this->ucmbihrisclient->send($request);
        return json_decode((string) $response->getBody()->getContents());
        //$result = json_decode($response->getBody()->getContents());
    }


    public function getData($endpoint, $method = "", $headers = [], $options = FALSE)
    {

        $url = BIO_URL . $endpoint;



        //do{
        $response = $this->client->request(
            $method,
            $url,
            [
                'headers' => $headers,
                'query' => [
                    "page" => $options->page,
                    "start_time" => $options->start_time,
                    "end_time" => $options->end_time,
                    "terminal_sn" => $options->terminal_sn,
                ]
            ]
        );

        return json_decode((string) $response->getBody()->getContents());
    }
    public function getTimeLogs($endpoint, $method = "", $headers = [])
    {
        // die(var_dump($this->get_jwt_auth()));

        $url = BIO_URL . $endpoint;

        //do{
        $response = $this->client->request(
            $method,
            $url,
            [
                'headers' => $headers

            ]
        );


        return json_decode((string) $response->getBody()->getContents());
    }
    public function getempData($endpoint, $method = "", $headers = [])
    {
        // die(var_dump($this->get_jwt_auth()));

        $url = BIO_URL . $endpoint;

        //do{
        $response = $this->client->request(
            $method,
            $url,
            [
                'headers' => $headers

            ]
        );


        return json_decode((string) $response->getBody()->getContents());
    }
    public function get_List($endpoint, $method = FALSE, $headers = [], $options = FALSE)
    {
        // die(var_dump($this->get_jwt_auth()));

        $url = BIO_URL . $endpoint;

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


        return json_decode((string) $response->getBody()->getContents());
    }


    public function sendAsync($endpoint = "", $method = "", $header = [], $body = [])
    {
        $headers = ['Content-Type' => 'application/json'];
        $headers = array_merge($headers, $header);
        // Send an asynchronous request.
        $request = new Request($method, $endpoint, $headers, json_encode($body));
        $promise = $this->client->sendAsync($request)->then(function ($response) {
            echo 'I completed! ' . $response->getBody();
        });
        $promise->wait();
        return $promise;

    }
    //http requests

    public function curlsendHttpPost($endpoint, $headers, $body)
    {
        $url = BIO_URL . $endpoint;
        $ch = curl_init($url);

        //post values
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        // Option to Return the Result, rather than just true/false
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // Set Request Headers
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers
        );
        //time to wait while waiting for connection...indefinite
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        curl_setopt($ch, CURLOPT_POST, 1);
        //set curl time..processing time out
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        // Perform the request, and save content to $result
        $result = curl_exec($ch);
        //curl error handling
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        if ($curl_errno > 0) {
            curl_close($ch);
            return "CURL Error ($curl_errno): $curl_error\n";
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        $decodedResponse = json_decode($result);
        return $decodedResponse;
    }
    public function curlsendiHRIS5HttpPost($endpoint, $headers, $body)
    {
        $url = iHRIS5_URL . $endpoint;
        $ch = curl_init($url);

        //post values
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers
        );
        //time to wait while waiting for connection...indefinite
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        curl_setopt($ch, CURLOPT_POST, 1);
        //set curl time..processing time out
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        // Perform the request, and save content to $result
        $result = curl_exec($ch);
        //curl error handling
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        if ($curl_errno > 0) {
            curl_close($ch);
            return "CURL Error ($curl_errno): $curl_error\n";
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        $decodedResponse = json_decode($result);
        return $decodedResponse;
    }
    public function curlupdateHttpPost($endpoint, $headers, $body)
    {
        $url = BIO_URL . $endpoint;
        $ch = curl_init($url);

        //post values
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        // Option to Return the Result, rather than just true/false
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // Set Request Headers
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers
        );
        //time to wait while waiting for connection...indefinite
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        curl_setopt($ch, CURLOPT_POST, 1);
        //set curl time..processing time out
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        // Perform the request, and save content to $result
        $result = curl_exec($ch);
        //curl error handling
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        if ($curl_errno > 0) {
            curl_close($ch);
            return "CURL Error ($curl_errno): $curl_error\n";
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        $decodedResponse = json_decode($result);
        return $decodedResponse;
    }

    public function curlgetHttp($endpoint, $headers, $body)
    {
        $url = BIO_URL . $endpoint;
        $ch = curl_init($url);

        //post values
        // curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
        // Option to Return the Result, rather than just true/false
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // Set Request Headers
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers
        );
        //time to wait while waiting for connection...indefinite
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        // curl_setopt($ch,CURLOPT_POST,1);
        //set curl time..processing time out
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        // Perform the request, and save content to $result
        $result = curl_exec($ch);
        //curl error handling
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        if ($curl_errno > 0) {
            curl_close($ch);
            return "CURL Error ($curl_errno): $curl_error\n";
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        $decodedResponse = json_decode($result);
        return $decodedResponse;
    }


}



?>