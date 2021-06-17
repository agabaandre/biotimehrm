         
<?php

$requests=Modules::run('requests/getAll');

?>



<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
  <div class="container-fluid">
      <div class="row">
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title; ?> <h3>
                      
                </div>
                <div class="panel-body">
                  <div class=" col-md-1" style="margin-left: 90%">
                      <a href="<?php echo base_url() ?>requests/print_request_report" style="" class="print btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>Print
                      </a>
                  </div>
                  <div class="row col-lg-12">

                    <table class="table table-striped table-bordered thistbl">
                        <thead class="tth" style="color: white;">
                          <th>#</th>
                          <th>Staff Name</th>
                          <th>Staff ID</th>
                          <th>Department</th>
                          <th>Request</th>
                          <th>Request Date</th>
                           <th>Duration</th>
                           <th>Action</th>
                        </thead>

                        <tbody>

                          <?php 

                          $i=1;
                          foreach($requests as $request):?>

                          <tr class="table-row tbrow content strow">
                            <td><?php echo $i ?></td>
                            <td><a ><?php echo $request->surname." ".$request->firstname." ".$request->othername; ?></a></td>
                            <td><?php echo str_replace('person|','',$request->ihris_pid); ?></td>
                            <td><?php echo $request->department; ?></td>
                            <td><?php echo $request->reason;?></td>
                            <td><?php echo date('j F, Y H:i', strtotime($request->date)); ?></td>
                            <td><?php echo "<b><i> from:</i></b>  ".date('j F, Y', strtotime($request->dateFrom))." "."<b><i>to:</b></i>  ".date('j F, Y H:i', strtotime($request->dateTo)); ?></td>
                            <td width="150px;">
                              <label class="badge"><?php echo $request->status; ?><hr style="margin:0.5px;"> <?php echo Modules::run('requests/getApprover',$request->approver); ?></label>
                            </td>
                          </tr>
                            <?php 
                            
                              $i++;
                              endforeach; 
                            ?>
                        </tbody>
                      </table>
                    
                  </div>

              </div>
          </div>
      </div>
    </div>
</div>

