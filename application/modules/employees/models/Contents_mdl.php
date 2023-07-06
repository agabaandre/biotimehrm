
<?php

      class Contents_mdl extends CI_Model{
            //Constructor
            public function __construct(){
                parent:: __construct();
                $this->load->database();
            }

            // get all content from here
            public function get_content($table_name, $id = FALSE, $value = FALSE){
                if(!$id){
                    $this->db->select('*');
                    $this->db->from($table_name);
                    $query = $this->db->get();
                    return $query->result();
                }else{
                    $query = $this->db->get_where($table_name, array($value => $id));
                    return $query->row_array();
                }
            }
      }