<?php 

class Rendercsv extends MX_Controller{
   //district data share
    public function renderdutyCsv()
    {
		ini_set('max_execution_time',0);
		ignore_user_abort(true);
		$filename='mohduty_summary'.'.csv';
		$filejson='mohduty_summary'.'.json';
        
        $this->db->select('person_id,dutydate,wdays,offs,mleave,other');
        $this->db->from('dutysummary');
        $qry= $this->db->get();
        $tbdata=$qry->result_array();

		$file = fopen('/home/mohhr/mohattshares/'.$filename, 'w+');
		$file2 = fopen('/home/mohhr/mohattshares/'.$filejson, 'w+');  

		$i=0;

		foreach ($tbdata as $data) {
			fputcsv($file, $data);
			fwrite($file2, json_encode($data, JSON_PRETTY_PRINT)); 

			$i++;
		}
		fclose($file);
		fclose($file2);
    echo $msg=  'CSV and Json for rosta generated Successfully';
	//print_r($tbdata);
	return $tbdata;

	}
	public function renderattCsv()
    { 
		ini_set('max_execution_time',0);
		ignore_user_abort(true);
		$filename='mohatt_summary'.'.csv';
		$filejson='mohatt_summary'.'.json';
        
        $this->db->select('ihris_pid,rdate,present,offduty,official,leaves');
        $this->db->from('att_summary');
        $qry= $this->db->get();
        $tbdata=$qry->result_array();

		$file = fopen('/home/mohhr/mohattshares/'.$filename, 'w+');
		$file2 = fopen('/home/mohhr/mohattshares/'.$filejson, 'w+');  

		$i=0;

		foreach ($tbdata as $data) {
			fputcsv($file, $data);
			fwrite($file2, json_encode($data, JSON_PRETTY_PRINT)); 

			$i++;
		}
		fclose($file);
		fclose($file2);
        echo $msg = 'CSV and Json for attendance generated Successfully';
   // print_r($tbdata);
   //return $tbdata;

	}




	

}
?>
