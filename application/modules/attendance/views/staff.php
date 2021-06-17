   

    <!-- Main content -->
  <section class="content">

      <div class="row">




<div class="col-md-12">
   <!-- general form elements -->
          <div class="panel">
            <div class="panel-heading with-border">
              <h3 class="box-title">Health Staff for -<?php echo $staffs[0]->facility; ?></h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
           
              <div class="panel-body" id='scheduletb'>
                <table class="table table-striped table-bordered thistbl">
                  <thead class="tth">
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Contact</th>
                     <th>National ID</th>
                      <th>IPPS No.</th>
                    
                    <!--<th width="13%"></th>-->
                  </thead>

                  <tbody>

                    <?php 

                    $no=0;

                    foreach($staffs as $staff) {
                            $no++;

                     ?>

                   

                    <tr class="strow" id="row<?php echo $staff->ihris_pid; ?>" >

                      <td data-label="NAME"><?php echo $staff->surname." ".$staff->firstname." ".$staff->othername; ?></td>
                      <td  data-label="POSITION"><?php echo $staff->job; ?></td>
                      <td  data-label="PHONE"><?php 
                     
                      if(!empty($staff->mobile)){

                       echo $staff->mobile; 
                        }else{
                          echo $staff->telephone; 

                           } 

                           ?></td>
                           <td  data-label="NIN"><?php echo $staff->nin; ?></td>
                           <td  data-label="IPPS"><?php echo $staff->ipps; ?></td>
                      <!--<td>

                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit<?php echo $no; ?>"><i class="glyphicon glyphicon-edit"></i></button>
                        

                      </td>-->
                    </tr>


<!--details/edit modal starts-->

<div class="modal fade" id="edit<?php echo $no; ?>" >
  <div class="modal-dialog modal-default">
    <div class="modal-content">
      <div class="modal-header">

 <h4 class="modal-title">Details for <?php echo $staff->surname." ".$staff->firstname." ".$staff->othername; ?> 
  <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>

      </div>
    <div class="modal-body">

<strong>
      

        <div class='row'><div class='col-md-4'>FIRSTNAME</div><div class='col-md-8'><?php echo $staff->firstname; ?></div></div>

        <hr style="margin:0.3em;">

        <div class='row'><div class='col-md-4'>LASTNAME</div><div class='col-md-8'> <?php echo $staff->surname; ?></div></div>

         <hr style="margin:0.3em;">


        <div class='row'><div class='col-md-4'>DESIGNATION</div><div class='col-md-8'><span class="label label-success "><?php echo $staff->job; ?></span></div></div>

         <hr style="margin:0.3em;">

        <div class='row'><div class='col-md-4'>DEPARTMENT</div><div class='col-md-8'><?php echo $staff->department; ?></div></div>

         <hr style="margin:0.3em;">

        <div class='row'><div class='col-md-4'>CONTACT</div><div class='col-md-8'><?php if(!empty($staff->mobile)){

                       echo $staff->mobile; 
                        }else{
                          echo $staff->telephone; 

                           }  ?></div></div>

        <hr style="margin:0.3em;">

            <div class='row'><div class='col-md-4'>NATIONAL ID</div><div class='col-md-8'><?php echo $staff->nin; ?></div></div>

         <hr style="margin:0.3em;">

             <div class='row'><div class='col-md-4'>IPPS No.</div><div class='col-md-8'><?php echo $staff->ipps; ?></div></div>

         <hr style="margin:0.3em;">
       
         <div class='row'><div class='col-md-4'>  IHRIS PERSON ID</div><div class='col-md-8'><?php echo $staff->ihris_pid; ?></div></div>
          
           
          
</strong>




</div><!--body-->
<div class="modal-footer">

              <button class="btn btn-info pull-right" data-dismiss="modal" type="button">Close</button>
        

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

        </div><!--col-md-12-->







            </div>
  <!-- /.content-row -->
   </section>
  


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
  var url='<?php echo base_url(); ?>schedules/add_schedule'

  $.ajax({url:url,
method:"post",
data:data,
success:function(res){

  console.log(res);

  $('.suc').html("<center>Schedule Added</center>");

  $('#reset').click();


}//success

}); // ajax



});//form submit



$('.delete').click(function(e){

  e.preventDefault();

  var id=$(this).attr('id');
  var url='<?php echo base_url(); ?>schedules/delete_schedule/'+id;

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