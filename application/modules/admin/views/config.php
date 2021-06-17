   

<?php 
// include_once("includes/head.php");
// include_once("includes/topbar.php");
// include_once("includes/sidenav.php");
// //include_once("");


?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
 

    <!-- Main content -->
        <section class="content">
          
       <div class="col-md-6">
             
            <div class="panel">
  <div class="panel-heading"><h4><?php echo $title; ?></h4>
  <span class="suc"></span></div>

   <div class="panel-body">
       
       <form method="post" id="config_form">
       
       
       <?php 
  $varss=$this->admin_model->get_vars();
     
       usort($varss,'rowid');

//print_r($varss);
       
       foreach($varss as $var1) { 
           
         if($var1['variable']=="Date to Fetch IHRIS Data"){
               
               $label=$var1['variable']." (Date of the Month e.g 10 or 02)";
               $width="2";
               $min="2";
           }else
           if($var1['variable']=="Duty Rosta Start Date"){
               
               $label=$var1['variable']." (Date in previous Month e.g 20 or 27)";
               $width="2";
               $min="2";
           }else
          
 if($var1['variable']=="Duty Rosta End Date"){
               
               $label=$var1['variable']." (Date in current  Month e.g 10 or 02)";
               $width="2";
               $min="2";
           }
          
          
 
           else{
               
               $label=$var1['variable'];
               
               $width="";
               $min="";
               
           }
       
       ?>
       
       <div class="form-group">
           <label><?php echo $label; ?></label>
           
           <input name="<?php echo $var1['rowid']; ?>" class="form-control" type="text" minlength="<?php echo $min;?>" maxlength="<?php echo $width;?>" value="<?php echo $var1['content']; ?>">
           
       </div>
       
       <?php } ?>
       
       <div class="form-group">
           
           <button class="btn btn-success" type="submit"><i class="fa fa-save"></i>Save Changes</button>
           
           </div>

</form>

</div>

</div>

</div>
<!-- END COL-6-->

                   <div class="col-md-6">
             
            <div class="panel">
  <div class="panel-heading"><h4>Upload iHRIS Biometric Users Report</h4>
  <span class="notif"><?php echo $this->session->flashdata('alert'); ?></span></div>

   <div class="panel-body">
       
       <form method="post" id="upload_form" action="<?php echo base_url(); ?>attendance/manualUpload" enctype="multipart/form-data">
           
       <input type="file" name="ihrisdata" class="file" placeholder=""/>
       
       <div class="form-group">
           <p></p>
           
         <button class="btn btn-primary" type="submit" onclick="uploadStarted()"><i class="fa fa-upload">Upload</i></button>
           
           </div>

</form>

</div>

</div>

</div>
           
   
   
   
   </section>
    <!-- /.section-->
  </div>
  
  <!-- /.content-wrapper -->
 <?php 
// include_once("includes/footermain.php");
// include_once("includes/rightsidebar.php");
// include_once("includes/footer.php");



?>

<script type="text/javascript">

  $(document).ready(function(){


$('#config_form').submit(function(e){

  e.preventDefault();

  var data=$(this).serialize();
  var url='<?php echo base_url(); ?>admin/configure'
  
  console.log(data);

  $.ajax({url:url,
method:"post",
data:data,
success:function(res){
    
    console.log(res);
    
    var alert='<div class="alert alert-info alert dismissible"><i class="fa fa-info-circle"></i> <a href="" class="pull-right" data-dismiss="alert">&times;</a> '+res+'</div>'
    
$('.suc').html(alert).fadeIn('slow');


setTimeout(function(){
    
    $('.suc').fadeOut('slow');
    
},4000)


}//success

}); // ajax



});//form submit






  });//doc
  



</script>
<script>

function uploadStarted(){

		
	$('.notif').html("<center><font color='green'><b>Upload in Progress Please Wait...</b></font></center>");
	
	
}

</script>
