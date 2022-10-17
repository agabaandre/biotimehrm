<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Auth_Token {

    protected $secret_key;
    protected $encrypt = ['HS256'];
    protected $token;
    protected $data;

    public function __construct() {
        $this->secret_key = 'qwerty123';
    }

    public function generateToken($data) {
        $header = $this->generateHeader();
        $payload = $this->generatePayload($data);
        $signature = $this->generateSignature($header, $payload);
        return $this->token = $header . "." . $payload . "." . $signature;
    }

    public function validateToken($token) {
        $token = explode('.', $token);
        $header = $token[0];
        $payload = $token[1];
        $signature = $token[2];
        $valid_signature = $this->generateSignature($header, $payload);
        if ($signature === $valid_signature) {
            $this->data = json_decode(base64_decode($payload));
            return true;
        }
        return false;
    }

    public function getData() {
        return $this->data;
    }

    protected function generateHeader() {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $header = json_encode($header);
        $header = base64_encode($header);
        return $header;
    }

    protected function generatePayload($data) {
        $payload = json_encode($data);
        $payload = base64_encode($payload);
        return $payload;
    }

    protected function generateSignature($header, $payload) {
        return hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
    }

}