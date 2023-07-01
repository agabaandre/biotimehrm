  
<?php

  function get_redis(){

    $ci =& gent_instance();
    $ci->load->library('redis', array('connection_group' => 'slave'), 'redis');

    return $ci->redis;
  }

  function set_str($key,$data){

   get_redis()->del($key);
   get_redis()->set($key,$data);

 }
 
 function get_data($key){

 	$cachedata = get_redis()->get($key);
 
   if($cachedata)
     return json_decode($cachedata );
 }
 
 function get_str($key){

 	return get_redis()->get($key);

 }
 
  function trash_data($key){

 	return get_redis()->del($key);
 }
 
 function clear_data(){
 
 		return get_redis()->command("flushall");
 }

 ?>
 	