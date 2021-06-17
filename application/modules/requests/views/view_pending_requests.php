
         
<?php

$user=$this->session->get_userdata();
$requests=Modules::run('requests/getPending',$user['ihris_pid'],'allow');

$reasons=Modules::run("reasons/getAll");

   $reasons_opt="";
     foreach($reasons as $reason): 
                  
    $reasons_opt.="<option value='".$reason->r_id."'>".$reason->reason."</option>";

    endforeach; 

//print_r($requests);

?>

<style type="">
   .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:40px;
    margin-bottom: :0;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>

<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
  <div class="container-fluid">
      <div class="row">
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title; ?> <h3>
                      
                </div>
                <div class="panel-body" id='request_tb'>
                    <center><span style="color:blue;"><?php echo $this->session->flashdata('msg'); ?></span></center>
                    <table class="table table-striped table-bordered thistbl" style="font-size:12px;"> 
                      <thead class="tth" style="color: white;">
                          <th>#</th>
                          <th>Request Date</th>
                          <th>Reason</th>
                          <th>Duration</th>
                          <th>Attachment</th>
                          <th>status</th>
                      </thead>
                      <tbody>

                          <?php  $i=1;  foreach($requests as $request):?>

                          <tr class="table-row tbrow content strow">
                            <td><?php echo $i ?></td>
                            <td><?php echo date('j F, Y H:i:s', strtotime($request->date)); ?></td>
                            <td><?php echo ucwords($request->reason);?></td>
                            <td><?php echo "<b><i> from:</i></b>  ".date('j F, Y', strtotime($request->dateFrom))." "."<b><i>to:</b></i>  ".date('j F,Y', strtotime($request->dateTo)); ?>
                            
                            <td>
                              <?php  if($request->attachment){ ?>
                                <a href="<?php echo base_url(); ?>assets/files/<?php echo $request->attachment; ?>" target="_blank"  style="" class="btn btn-success btn-sm btn-outline">
                                  <i class="fa fa-file"></i>
                                </a>
                              <?php  } ?>
                            </td>
                              
                            </td>
                            
                            <td>
                             
                                
                            <label class="badge"><?php echo $status=$request->status; ?></label>
                                <?php
                                 $cr=$request->schedule_id;
                                 $myleave=Modules::run("requests/getLeave");
                                 // print_r($myleave);
                                 if($status=="Pending"){
                                if (in_array($cr, $myleave))
                                    { ?>
                                      <a href="<?php echo base_url()?>leaveform/Leaveform/leaveApplication/<?php echo $request->entry_id; ?> " style="" class="print btn btn-success btn-sm btn-default"><i class="fa fa-file" aria-hidden="true">Leave Form</i>
                                      </a>
                                   <?php } ?>
                                   
                                <?php  if($request->clarification=="1"){ ?>
                                <a data-target="#editModal<?php echo $request->entry_id; ?>" data-toggle="modal" style="" class="print btn btn-success btn-sm btn-default"><i class="fa fa-question" aria-hidden="true">Clarify</i>
                                </a>

                                <?php   }  ?>

                                
                                <?php if ($status=='Pending'){ ?>
                                  <a data-target="#cancelr<?php echo $request->entry_id; ?>" data-toggle="modal" style="" class="print btn btn-success btn-sm btn-danger"><i class="fa fa-times" aria-hidden="true">Cancel</i>
                                </a>

                              <?php   } } ?>
                               
                            </td>
                          </tr>
                            <?php include 'edit_request_modal.php'; $i++;  endforeach;  ?>
                          </tbody>
                  </table>
              </div>
          </div>
      </div>
    </div>
</div>

