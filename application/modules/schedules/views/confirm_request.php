   

<?php 
include_once("includes/head.php");
include_once("includes/topbar.php");
include_once("includes/sidenav.php");
//include_once("");


?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
 

    <!-- Main content -->
        <section class="content">
      <!-- Small boxes (Stat box) -->
             <div class="row">






<div class="col-md-8">
   <!-- general form elements -->
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Existing Schedules</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
           
              <div class="box-body" >
                <table class="table table-striped thistbl">
              
                    
                    
                  <thead>
                    <th>Schedule</th>
                    <th>Letter</th>
                    <th>Starts</th>
                    <th>Ends</th>
                    <th width="13%"></th>
                  </thead>

                  <tbody>

                    <?php foreach($schedules as $schedule) { ?>

                    <tr id="row<?php echo $schedule['schedule_id']; ?>">
                     <td><?php echo $schedule['schedule']; ?></td>
                      <td><?php echo $schedule['letter']; ?></td>
                      <td><?php echo date('h:s A',strtotime($schedule['starts'])); ?></td>
                      <td><?php echo date('h:s A',strtotime($schedule['ends'])); ?></td>
                      <td>
                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit<?php echo $schedule['schedule_id']; ?>"><i class="glyphicon glyphicon-edit"></i></button>
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#del<?php echo $schedule['schedule_id']; ?>"><i class="glyphicon glyphicon-trash"></i></button>

                      </td>
                    </tr>
<!--delete modal starts-->
<div class="modal fade" id="del<?php echo $schedule['schedule_id']; ?>">
  <div class="modal-dialog modal-sm modal-default" style="margin-top: 6%;">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Confirm Action <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
      </div>
    <div class="modal-body">

      <span id="dela<?php echo $schedule['schedule_id']; ?>"></span>

<p><i class="glyphicon glyphicon-alert"></i>&nbsp; You're Deleting <b style="color: #000;"><?php echo $schedule['schedule']; ?> </b></p>
</div><!--body-->
<div class="modal-footer">

  <button class="btn btn-danger  btn-sm delete" id="<?php echo $schedule['schedule_id']; ?>"><i class="glyphicon glyphicon-trash"></i> Yes, Delete</button>

  <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>

  </div>
</div><!--content-->
</div><!--modal dialogu-->
</div><!--modal-->

<!--details/edit modal starts-->

<div class="modal fade" id="edit<?php echo $schedule['schedule_id']; ?>">
  <div class="modal-dialog modal-default">
    <div class="modal-content">
      <div class="modal-header">

 <h4 class="modal-title">Edit Schedule <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>

      </div>
    <div class="modal-body">

<form id="update_schedule">


 <div class="form-group">
                  <label for="exampleInputEmail1">Schedule Name</label>
                  <input type="text" class="form-control" id="upschedule" name="schedule" value="<?php echo $schedule['schedule']; ?>" placeholder="Enter Schedule">
                </div>
                <div class="form-group">
                  <label for="letter">Letter</label>
                  <input type="text" class="form-control" id="upletter" name="letter" value="<?php echo $schedule['letter']; ?>" placeholder="e.g A">
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                  <label for="letter">Starts</label>
                  <div class="input-group boostrap-timepicker timepicker">
                  <input type="text" class="form-control time"  value="<?php echo $schedule['starts']; ?>" data-provide="timepicker"  data-minute-step="15" name="starts" placeholder="e.g 08:00AM">


                </div>
                </div>
                </div>


                 <div class="col-md-6">
                  <div class="form-group">
                  <label for="letter">Ends</label>
                  <input type="text" class="form-control time" name="ends" value="<?php echo $schedule['ends']; ?>" placeholder="e.g 05:00PM">
                </div>
                </div>


</form>

</div><!--body-->
<div class="modal-footer">

    <div class="col-md-6">
              <button class="btn btn-success pull-right" type="submit">Save Schedule</button>
           </div>

                <div class="col-md-4">
              <button class="btn btn-default pull-right" data-dismiss="modal" type="button">Cancel Edit</button>
            </div>

  </div>
</div><!--content-->
</div><!--modal dialogu-->
</div><!--modal-->


                        <?php } ?>


                  </tbody>


                </table>
               

            
              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                
              </div>
         


          </div>
          <!-- /.box -->

        </div><!--col-md-8-->



        <div class="col-md-4">
   <!-- general form elements -->
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Add Schedule</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" id="schedule_form">
              <div class="box-body">

                <span class="suc"></span>

                <div class="form-group">
                  <label for="exampleInputEmail1">Schedule Name</label>
                  <input type="text" class="form-control" id="schedule" name="schedule" placeholder="Enter Schedule">
                </div>
                <div class="form-group">
                  <label for="letter">Letter</label>
                  <input type="text" class="form-control" id="letter" name="letter" placeholder="e.g A">
                </div>
                
                
                <div class="form-group">
                  <label for="letter">Usage</label>
              <select name="purpose" class="form-control">
                  
                  <option value="r">Rota</option>
                  <option value="a">Daily Attendance</option>
              </select>
              
              
                </div>
                

                <div class="col-md-6">
                  <div class="form-group">
                  <label for="letter">Starts</label>
                  <div class="input-group">

                  <input type="text" class="form-control timepicker" id="starts" name="starts" placeholder="e.g 08:00AM">


                </div>
                </div>
                </div>


                 <div class="col-md-6">
                  <div class="form-group">
                  <label for="letter">Ends</label>
                  <input type="text" class="form-control time" id="ends" name="ends" placeholder="e.g 05:00PM">
                </div>
                </div>

            
              </div>
              <!-- /.box-body -->

              <div class="box-footer">

            

             <div class="col-md-6">
              <button class="btn btn-success pull-right" type="submit">Save Schedule</button>
            </div>

                <div class="col-md-6">
              <button class="btn btn-default pull-right" id="reset" type="reset">Reset</button>
            </div>

              </div>
            </form>
          </div>
          <!-- /.box -->

        </div><!--col-md-4-->






            </div>
  <!-- /.content-row -->
   </section>
    <!-- /.section-->
  </div>
  
  <!-- /.content-wrapper -->
 <?php 
include_once("includes/footermain.php");
include_once("includes/rightsidebar.php");
include_once("includes/footer.php");



?>

<script type="text/javascript">

  $(document).ready(function(){

$('#scheduletbl').slimscroll({
  height: '400px',
  size: '5px'
});


$('.timepicker').timepicker({showInputs:false});




$('#schedule_form').submit(function(e){

  e.preventDefault();

  var data=$(this).serialize();
  var url='<?php echo base_url(); ?>attendance/add_schedule'

  $.ajax({url:url,
method:"post",
data:data,
success:function(res){

  console.log(res);
  
  if(res=="Schedule Added"){

  $('.suc').html("<center><font color='green'>Schedule Added</font></center>");
    $('#reset').click();

}

else{

  $('.suc').html("<center><font color='red'>"+res+"</font></center>");
 
}



  


}//success

}); // ajax



});//form submit



$('.delete').click(function(e){

  e.preventDefault();

  var id=$(this).attr('id');
  var url='<?php echo base_url(); ?>attendance/delete_schedule/'+id;

  $.ajax({url:url,
success:function(res){

  console.log(res);

  $('#row'+id).remove();

  $('#dela'+id).html("<font color='green'>"+res+"</font>");

  setTimeout(function(){

    $('#dela'+id).html("");

    $('#del'+id).modal('hide');
  },1500);


}//success

}); // ajax



});//form submit


  });//doc
  



</script>