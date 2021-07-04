 <!-- Main content -->
 <style>
 li{
   text-decoration: none;

 }
 </style>
 <section class="content">
      <div class="container-fluid">
        <!-- Main row -->

        <div class="row">
          <section class="col-lg-12 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->


           

            <div class="callout callout-success">
              <h5><i class="fas fa-file"></i> Attendance Reports</h5>
              
            </div>
            <div class="card">


            <ol>
                      <li><a href="<?php echo base_url()?>rosta/actualsreport">Monthly Daily Attendance Report</a></p>-Monthly Health Worker Attendance Sheet</li>
                      <li><a href="<?php echo base_url()?>employees/timesheet">Monthly Timesheet</a><p> -Number of hours per for Each Health Worker in A month</p></li>
                      <li><a href="<?php echo base_url()?>employees/personlogs">Person Attendnce</a><p> - Printable Summary of Attedance to Duty by Employee</p></li>
                      <li><a href="<?php echo base_url()?>employees/viewTimeLogs">Daily Time Log Report </a><p> -Daily Time Logs </p></li>
                      <li><a href="<?php echo base_url()?>employees/groupedTimeLogs">Group Monthly Time Log Report </a><p> -Group Monthly Time Logs </p></li>
                      <li><a href="<?php echo base_url()?>attendance/attendance_summary">Attendance Summary </a><p> -Aggregated by P - Present, O - Off Duty, L - Leave, A - Absent </p></li>

					 
                     			
				   </ol>
              



            </div>
            <!-- /.card -->

            <div class="callout callout-info">
              <h5><i class="fas fa-file"></i> Schedule Reports</h5>
              
            </div>
            <div class="card">


            <ol>
                      <li><a href="<?php echo base_url()?>rosta/fetch_report">Monthly Duty Roster Report</a></p>-Monthly Health Worker Schedule Sheet</li>
                      <li><a href="<?php echo base_url()?>rosta/summary">Duty Roster Summary</a><p> - Summarised duty roster report</p></li>
                       
                      
					 
                     			
				   </ol>


              



            </div>
            <!-- /.card -->

            <div class="callout callout-warning">
              <h5><i class="fas fa-file"></i>Attendance & Duty Roster Analysis</h5>
              
            </div>
            <div class="card">


            <ol>
                      <li><a href="<?php echo base_url()?>">Duty Roster Reporting Rate </a></p>- Duty Roster reporting rate by, National, Region, District, Facility </li>      
                      <li><a href="<?php echo base_url()?>">Attendance Reporting Rate </a></p>- Duty Roster reporting rate by National, Region, District, Facility </li>  
                            
                      
                			
				   </ol>
              



            </div>
            <!-- /.card -->

            <div class="callout callout-danger">
              <h5><i class="fas fa-file"></i> Other Reports</h5>
              
            </div>
            <div class="card">


            <ol>

             <?php // print_r($setting); ?>
                      <li><a href="<?php echo base_url()?>workshops/checkins">Out of Station Checkin  </a></p>-Log showing the field staff who are attending to their activities </li>        
                      <li><a href="<?php echo base_url()?>workshops/linkedDevices">Mobile Check In Devices </a></p>-Out of station Mobile Check In Devices </li>
                      <li><a href="<?php echo base_url()?>requests/viewMySubmittedRequests">Out of Station Requests Sent</a><p> - Requests for out of station</p></li>
                      <?php if(in_array('30', $permissions)){ ?>
                      <li><a href="<?php echo base_url()?>requests/leaveRequests">Leave Requests</a><p> - Leave Requests</p></li>
                      <?php } ?>
                      <?php if(in_array('21', $permissions)){ ?>
                      <li><a href="<?php echo base_url()?>requests/requestReport">Requests and Approvals Report</a><p> - Shows the requests received from employees and their status</p></li>
                      <?php }?>
                      <?php if(in_array('32', $permissions)){ ?>
                      <li><a href="<?php echo base_url()?>reports/biotimesync">Bio-time Sync Status</a><p> - Device Synchronisation Logs by Date</p></li>   
                      <li><a href="<?php echo base_url()?>attendance/fingerprints">Mobile Phone Enrolled Users</a><p> - User Enrolled to Use attendance on Mobile</p></li>  
					            <?php }?>
                     			
				   </ol>
              



            </div>
            <!-- /.card -->

          
          </section>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->