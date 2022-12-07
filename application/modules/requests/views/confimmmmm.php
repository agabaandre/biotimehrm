 <div class="content">

          <div class="panel">
            <br>
            <div class="col-md-11">
               <div class="panel-heading">
                  <h4><?php echo $title; ?></h4>
                  <span class="suc pull right"></span>
              </div>
            </div>

            <div class=" col-md-1 pull-right">
               <?php if(count($requests)>0){ ?>
                <a href="<?php echo base_url() ?>requests/print_request_report" style="" class="print btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>Print
                </a><?php } ?>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
          
              <div class="panel-body" id='request_tb'>
                <table class="table table-striped table-bordered thistbl">
                  <thead class="tth">
                    <th>#</th>
                    <th>Staff Name</th>
                    <th>Staff .ID</th>
                    <th>Facility</th>
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
                      <td><?php echo $request->ihris_pid; ?></td>
                      <td><?php echo $request->facility_id; ?></td>
                      <td><?php echo $request->reason;?></td>
                      <td><?php echo $request->date; ?></td>
                      <td><?php echo "<b><i> from:</i></b>  ".$request->dateFrom." "."<b><i>to:</b></i>  ".$request->dateTo; ?></td>
                      <td width="150px;">

                        <?php if($request->status=="Pending"){ ?>
                        <a href="<?php echo base_url(); ?>requests/acceptRequest/<?php echo str_replace('|','_',$request->entry_id); ?>" style="" class="print btn btn-success btn-sm btn-outline">Accept
                        </a>
                        <a href="<?php echo base_url(); ?>requests/rejectRequest/<?php echo str_replace('|','_',$request->entry_id); ?>" style="" class="print btn btn-danger btn-sm btn-outline" >Reject
                        </a>
                      <?php } else{ ?>

                        <label class="badge"><?php echo $request->status; ?></label>

                      <?php } ?>

                      </td>
                    </tr>
                      <?php 
                        $i++;
                        
                        endforeach; 

                        if(count($requests==0)){

                           echo "<tr><td colspan='8'><center><h3 class='text-danger'>No pending requests</h3></center></td></tr>";
                        }
                      ?>
                  </tbody>
                </table>
              </div>
            </div>
  </div>
