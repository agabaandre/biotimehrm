         
<?php

$user=$this->session->get_userdata();
$requests=Modules::run('requests/getPending');
//echo $user['role'];

?>

<!--link href="<?php echo base_url(); ?>assets/css/dataTables/datatables.min.css" rel="stylesheet" -->
<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
  <div class="container-fluid">
      <div class="row">
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title; ?><h3>
                      
                </div>
                <div class="panel-body">

                  <div class="table-responsive">
                      <table class="table table-striped table-bordered table-hover " >
                            <thead class="tth" style="color: white;">
                              <tr>
                                  <th>#</th>
                                  <th>Staff Name</th>
                                  <th>Department</th>
                                  <th>Request</th>
                                  <th>Request Date</th>
                                  <th>Duration</th>
                                  <th>Document</th>
                                  <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                                <?php 
                                  $i=1;
                                  foreach($requests as $request):?>

                                <tr class="table-row tbrow content strow">
                                    <td><?php echo $i ?></td>
                                    <td><a ><?php echo $request->surname." ".$request->firstname." ".$request->othername; ?></a></td>
                                    <td><?php echo $request->department; ?></td>
                                    <td><?php echo $request->reason;?></td>
                                    <td><?php echo $request->date; ?></td>
                                    <td><?php echo "<b><i> from:</i></b>  ".$request->dateFrom." "."<b><i>to:</b></i>  ".$request->dateTo; ?></td>
                                    <td></td>
                                    <td width="150px;">
                                    <!--td><?php ?></td -->

                                      <?php //if($request->status=="Pending"){

                                       // if($user['ihris_pid']!==$request->ihris_pid){

                                       ?>
                                      <a href="<?php echo base_url(); ?>requests/acceptRequest/<?php echo str_replace('|','_',$request->entry_id); ?>" style="" class="print btn btn-success btn-sm btn-outline">Accept
                                      </a>
                                      <a href="<?php echo base_url(); ?>requests/rejectRequest/<?php echo str_replace('|','_',$request->entry_id); ?>" style="" class="print btn btn-danger btn-sm btn-outline" >Reject
                                      </a>
                                        <?php //}else{ ?>
                                           <label class="badge"><?php echo $request->status; ?></label>
                                          <?php
                                        //} }

                                        //else{ ?>

                                          <label class="badge"><?php //echo $request->status; ?></label>

                                    <?php //} ?>

                                    </td>
                                </tr>
                                  <?php 
                                      $i++;
                                    endforeach; 

                                    if(count($requests)==0){

                                        echo "<tr><td colspan='8'><center><h3 class='text-danger'>No pending requests</h3></center></td></tr>";
                                    }
                                        ?>
                            </tbody>
                            <tfoot>
                                
                            </tfoot>
                      </table>
                  </div>
              </div>
          </div>
      </div>
    </div>
</div>

<!--script src="<?php echo base_url(); ?>assets/js/dataTables/dTb/datatables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/dataTables/dTb/dataTables.bootstrap4.min.js"></script -->

