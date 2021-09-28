<?php 

Class Message_Model extends CI_Model
{
    public function get_conversation($conversationId)
    {

        $response = array();

        $this->db->select('conversationId, message, fromPersonId, toPersonId, type, created_at AS timestamp');
        $this->db->from('messages');
        $this->db->where('conversationId', urldecode($conversationId));
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $messageList = $query->result();

            $response['status'] = 'SUCCESS';
            $response['message'] = 'Conversations loaded';
            $response['error'] = FALSE;
            $response['messageList'] = $messageList;
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'No conversations';
            $response['error'] = FALSE;
        }

        return $response;
    }

    public function post_conversation($conversation) 
    {
        $response = array();

        $this->db->insert('messages', $conversation);
        if($this->db->affected_rows() === 1) {
            $response['status'] = 'SUCCESS';
            $response['message'] = 'Message Sent';
            $response['error'] = FALSE;
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Mesaage not sent';
            $response['error'] = FALSE;
        }

        return $response;
    }

    public function get_chat_users() {
        $this->db->select('ihris_pid AS personId, name AS personName');
        $this->db->from('user');
        $this->db->where('ihris_pid is NOT NULL', NULL, FALSE);
        $this->db->where('ihris_pid <> " "', NULL, FALSE);
        $query = $this->db->get();
        if($query->num_rows() > 0 ) {
            $response['status'] = 'SUCCESS';
            $response['message'] = "Data loaded";
            $response['error'] = FALSE;
            $response['approvalChatUserList'] = $query->result();
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = "No users found";
            $response['error'] = FALSE;
        }

        return $response;

    }
}