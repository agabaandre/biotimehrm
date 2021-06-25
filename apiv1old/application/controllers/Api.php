<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

Class Api extends REST_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_Model', 'authHandler');
        $this->load->model('Reason_Model', 'reasonHandler');
        $this->load->model('Request_Model', 'requestHandler');
        $this->load->model('Workshop_Model', 'workshopHandler');
        $this->load->model('Message_Model', 'messageHandler');
    }
    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        $response = array();

        $hasErrors = FALSE;

        if(!isset($username) || empty($username)) {
            $response['status'] = 'USERNAME_ERROR';
            $response['message'] = 'You must provide a username';
            $response['error'] = TRUE;

            $hasErrors = TRUE;
        }

        if(!isset($password) || empty($password)) {
            $response['status'] = 'PASSWORD_ERROR';
            $response['message'] = 'You must provide a password';
            $response['error'] = TRUE;

            $hasErrors = TRUE;
        }

        if($hasErrors) {
            $this->response($response, 400);
        } else {
            $results = $this->authHandler->login($username, $password);
            $this->response($results);
        }
    }

    public function reasons_get() 
    {
        $this->response($this->reasonHandler->get_reasons());
    }

    public function request_details_get($requestId)
    {
        $this->response($this->requestHandler->get_request_details($requestId));
    }

    //SUBMIT REQUEST
    public function submit_request_post()
    {
        $today=date('Y-m-d');
        if($this->post('dateFrom') < $today){
            $this->response(
                array(
                    'status'=> 'FAILED', 
                    'error'=>TRUE, 
                    'message'=>'Date From is a past date'), 
                    REST_Controller::HTTP_OK);
        }

        if ($this->post('dateTo') < $today) {
            $this->response(array(
                'error'=> TRUE, 
                'status'=>'FAILED', 
                'message'=>'Date To is a past date '
            ), REST_Controller::HTTP_OK);
        }

        if ($this->post('dateTo') < $this->post('dateFrom')) {
            $this->response(array(
                'error'=> TRUE, 
                'status'=>'FAILED', 
                'message'=>'[Date To] must be a current or a future date '), 
                REST_Controller::HTTP_OK);
        }
        if (empty($this->post('personId'))) {
           $this->response(array(
               'error'=> TRUE, 
               'status'=>'FAILED', 
               'message'=>'IHRIS PERSON ID is required to make request'), 
               REST_Controller::HTTP_OK);
       }
     else {
    //  call model
        $result = $this->requestHandler->post_request($this->post());
        if($result) {
             $this->response(
                 array( 
                     'error' => FALSE, 
                     'status'=> 'SUCCESS',
                     'message'=>'Request Submitted'),
                    REST_Controller::HTTP_CREATED);
         } else {
             $this->response(
                 array('error'=> TRUE, 
                 'status'=>'FAILED', 
                 'message'=>'Request submission failed'), 
             REST_Controller::HTTP_OK);
         }
    }
  }

    //GET PENDING REQUESTS
    public function pending_requests_get($personId = null) 
    {
        if(isset($personId) && !empty($personId)) {
            $results = $this->requestHandler->get_pending_requests($personId);
            $this->response($results, REST_Controller::HTTP_OK);

        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Provide person ID as request path {personId}';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }

    //GET SINGLE PENDING REQUEST
    public function pending_request_details_get($entryId = null) 
    {
        if(isset($entryId) && !empty($entryId)) {
            $results = $this->requestHandler->get_pending_request_details($entryId);
            $this->response($results, REST_Controller::HTTP_OK);

        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Provide person ID as request path {personId}';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }

    //APPROVE REQUEST
    public function approve_request_post(){
        $entryid = $this->input->post();
        $entryid=$entryid['entry_id'];
        $sendRequest=$this->requestHandler->approveRequest($entryid);
        if ($sendRequest){
            
            $results = array('status'=>'SUCCESS', 'error'=> FALSE, 'message'=> 'Request Approved');
            $this->response($results, REST_Controller::HTTP_CREATED);
        } else {
            $results = array('status'=>'FAILED', 'error'=> TRUE, 'message'=> 'Failed to APPROVE');
            $this->response($results, REST_Controller::HTTP_OK);
        }

    }

    //DECLINE REQUEST
    public function reject_request_post(){
        $entryid = $this->input->post();
        $entryid=$entryid['entry_id'];
        $sendRequest=$this->requestHandler->rejectRequest($entryid);
        if ($sendRequest){
            
            $results = array('status'=>'SUCCESS', 'error'=> FALSE, 'message'=> 'Request Declined');
            $this->response($results, REST_Controller::HTTP_CREATED);
        } else {
            $results = array('status'=>'FAILED', 'error'=> TRUE, 'message'=> 'Failed to DECLINE');
            $this->response($results, REST_Controller::HTTP_OK);
        }
	}

    //GET APPROVED REQUESTS
    public function approved_requests_get($personId = null) 
    {
        if(isset($personId) && !empty($personId)) {
            $results = $this->requestHandler->get_approved_requests($personId);
            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Provide person ID as request path {personId}';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }

    public function workshop_dates_get($personId = null)
    {
        if($personId != null) {
            $results = $this->workshopHandler->get_workshop_dates($personId);
        } else {
            $results = array();
        }

        if(isset($results) && !empty($results)) {
            $response = array('buttonStatus'=>'ENABLED', 'requestId'=>$results->entry_id);
        } else {
            $response = array('buttonStatus'=>'DISABLED', 'requestId'=>'');
        }

        $this->response($response, REST_Controller::HTTP_OK);
        
    }

    public function workshopdata_post()
    {
        $userdata = $this->input->post();
        $results = $this->workshopHandler->post_workshop_data($userdata);

        if(isset($results) && !empty($results)) {
            $response = array('buttonStatus'=>'ENABLED');
        } else {
            $response = array('buttonStatus'=>'DISABLED');
        }

        $this->response($response, REST_Controller::HTTP_OK);

    }

    //GET CHAT USERS
    public function approval_chat_users_get()
    {
            $results = $this->messageHandler->get_chat_users();
            $this->response($results, 200);
    
    }

    //GET MESSAGES
    public function conversation_get($conversationId)
    {
        if(!isset($conversationId) || $conversationId == NULL) {
            $response['status'] == 'FAILED';
            $response['message'] = 'Invalid ID provided';
            $response['error'] = true;

            $this->response($response, 400);
        } else {
            $results = $this->messageHandler->get_conversation($conversationId);
            $this->response($results, 200);
        }
    }

    //POST MESSAGE
    public function conversation_post()
    {
        $conversation = $this->post();
        $result = $this->messageHandler->post_conversation($conversation);
        $this->response($result);
    }
}