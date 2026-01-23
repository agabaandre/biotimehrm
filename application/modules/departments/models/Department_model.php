<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Department_model extends CI_Model
{   
    protected $table;
    protected $department;
    protected $division;
    protected $unit;
    protected $facility;
    protected $user;
    protected $filters;
    protected $ufilters;
    protected $distfilters;

   public function __construct(){

                parent::__construct();

                $this->table="departments";
                $this->department=$this->session->userdata['department_id'];
        }

    //gets all departments from the department table
    public function getAll_departments(){
      try {
        // Reset query builder
        $this->db->reset_query();
        
        $qry = $this->db->query("SELECT distinct department from ihrisdata WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");

        if ($qry) {
          return $qry->result();
        } else {
          log_message('error', 'getAll_departments query failed');
          return array();
        }
      } catch (Throwable $e) {
        log_message('error', 'Error in getAll_departments: ' . $e->getMessage());
        return array();
      }
    } 


    public function get_departments(){
        //$this->db->distinct('department_id');
        $this->db->distinct('department');
        $this->db->select('department,department_id');
        $this->db->order_by('department', 'ASC');
        $qry=$this->db->get('ihrisdata');

        $results=$qry->result();

        return $results;
    }

    public function get_divisions($department){

        $this->db->distinct('division');
        $this->db->select('division');
        $this->db->where('department_id',$department);
        $this->db->order_by('division', 'ASC');
        $qry=$this->db->get('ihrisdata');
        $divisions = array();
 
        if($qry->result()){
            foreach ($qry->result() as $division) {
                $divisions[$division->division] = $division->division;
            }
            return $divisions;
        } else {
            return FALSE;
        }
    }


   public function countDepart(){

    $facility=$this->facility;

    $department=$this->department;
    $division=$this->division;
    $unit=$this->unit;


  if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){


            if(!empty($department)){
                $this->db->where('department_id',$department);
                
            }
           

            if(!empty($division)){
                $this->db->where('division',$division);
                
            }


            if(!empty($unit)){
                 $this->db->where('unit',$unit);
                
            }
            if(!empty($facility)){
                $this->db->where('facility_id',$facility);
               
           }
          
            
           
    
  }
   		$this->db->select('department');
   		$this->db->distinct('department');
   		$query=$this->db->get('ihrisdata');
   		$count=$query->num_rows();
   		return $count;

   }

   

   public function save_department(){

        $data=array(
        'department_id'=>$this->input->post('department_id'),
        'department'=>$this->input->post('department')
        );

        $qry=$this->db->insert($this->table, $data);
        $rows=$this->db->affected_rows();

        if($rows>0){

          return "Department has been Added Successfully";
        }

        else{

          return "Operation failed";
        }

  }

  public function updateDepartment($postdata){

      $dpt_id=$postdata['dprt_id'];
    $this->db->where('dprt_id',$dpt_id);

    $this->db->update($this->table,$postdata);
    $rows=$this->db->affected_rows();

    if($rows>0){
      return "The ".$postdata['department']." "." district has been updated";
    }

    else{

      return "No Operation made, seems like no changes made";
    }
  }



  public function deleteDepartment(){

    $data=$this->input->post('dprt_id');
    $this->db->where('dprt_id',$data);

    $this->db->delete($this->table, $data);
    $rows=$this->db->affected_rows();

    if($rows>0){

      return "The ".$data['department']." "." district has been updated";
    }

    else{

      return "No Operation made, seems like no changes made";
    }

  }






   
}
    
