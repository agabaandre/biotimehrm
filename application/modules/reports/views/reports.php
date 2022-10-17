 <style>
   li {
     text-decoration: none;
   }
 </style>
 <section class="content">
   <div class="container-fluid">
     <!-- Main row -->
     <div class="row">
       <section class="col-lg-12 connectedSortable">
         <!-- Custom tabs (Charts with tabs)-->
         <?php
          $userdata = $this->session->get_userdata();
          $permissions = $userdata['permissions'];
          ?>
         <div class="callout callout-success">
           <h5><i class="fas fa-file"></i> Attendance Reports</h5>

         </div>
         <div class="card">


           <ol>

             <li><a href="<?php echo base_url() ?>rosta/attfrom_report">Monthly Daily Attendance Report</a></p>-Monthly Health Worker Attendance Sheet</li>
             <li><a href="<?php echo base_url() ?>attendance/attendance_summary">Attendance Summary </a>
               <p> -Aggregated by P - Present, O - Off Duty, L - Leave, X - Absent </p>
             </li>
             <li><a href="<?php echo base_url() ?>employees/timesheet">Monthly Timesheet</a>
               <p> -Number of hours worked by each employee in a month</p>
             </li>
             <li><a href="<?php echo base_url() ?>employees/personlogs">Person Attendnce</a>
               <p> - Printable Summary of Attedance to Duty by Employee</p>
             </li>
             <li><a href="<?php echo base_url() ?>reports/person_attendance_all">District Person Attendnce All</a>
               <p> - Printable Summary of Attedance to Duty by Employee</p>
             </li>
             <li><a href="<?php echo base_url() ?>employees/viewTimeLogs">Daily Time Log Report </a>
               <p> -Daily Time Logs </p>
             </li>
             <li><a href="<?php echo base_url() ?>employees/groupedTimeLogs">Monthly Time Log Report </a>
               <p> -Monthly Time Logs </p>
             </li>
             <li><a href="<?php echo base_url() ?>reports/average_hours">Average Monthly Hours </a>
               <p> - Facility Monthly Average Time Logs </p>
             </li>
             <li><a href="<?php echo base_url() ?>reports/attendance_aggregate">Attendance by Aggregates </a>
               <p> - Attendance by Institution Type, Region, District, Facility, Facility Level </p>
             </li>
             <li><a href="<?php echo base_url() ?>reports/person_attendance_all">Person Attendance All </a>
               <p> - Attendance for all Individuals Viewbale by Facility, District, Region</p>
             </li>


           </ol>




         </div>
         <!-- /.card -->

         <div class="callout callout-info">
           <h5><i class="fas fa-file"></i> Schedule Reports</h5>
         </div>
         <div class="card">
           <ol>
             <li><a href="<?php echo base_url() ?>rosta/fetch_report">Monthly Duty Roster Report</a></p> - Monthly Health Worker Schedule Sheet</li>
             <li><a href="<?php echo base_url() ?>rosta/summary">Duty Roster Summary</a>
               <p> - Summarised duty roster report</p>
             </li>
           </ol>
         </div>
         <!-- /.card -->

         <div class="callout callout-warning">
           <h5><i class="fas fa-file"></i>Attendance & Duty Roster Analysis</h5>

         </div>
         <div class="card">


           <ol>
             <li><a href="<?php echo base_url('reports/rosterRate') ?>">Duty Roster Reporting Rate </a></p>- Duty Roster reporting rate by Facility </li>
             <li><a href="<?php echo base_url('reports/attendanceRate') ?>">Attendance Reporting Rate </a></p>- Duty Roster reporting rate by Facility </li>
             <li><a href="<?php echo base_url('reports/attendroster') ?>">Duty Roster Vs Attendance </a></p>- Comparison of days on Day,Evening and Days Present at the Facility </li>



           </ol>




         </div>
         <!-- /.card -->

         <div class="callout callout-danger">
           <h5><i class="fas fa-file"></i> Other Reports</h5>

         </div>
         <div class="card">
           <ol>
             <?php // print_r($setting); 
              ?>
             <li><a href="<?php echo base_url() ?>workshops/checkins">Out of Station Checkin </a></p>-Log showing the field staff who are attending to their activities </li>
             <li><a href="<?php echo base_url() ?>workshops/linkedDevices">Mobile Check In Devices </a></p>-Out of station Mobile Check In Devices </li>
             <li><a href="<?php echo base_url() ?>requests/viewMySubmittedRequests">Out of Station Requests Sent</a>
               <p> - Requests for out of station</p>
             </li>
             <?php if (in_array('30', $permissions)) { ?>
               <li><a href="<?php echo base_url() ?>requests/leaveRequests">Leave Requests</a>
                 <p> - Leave Requests</p>
               </li>
             <?php } ?>
             <?php if (in_array('21', $permissions)) { ?>
               <li><a href="<?php echo base_url() ?>requests/requestReport">Requests and Approvals Report</a>
                 <p> - Shows the requests received from employees and their status</p>
               </li>
             <?php } ?>
             <?php if (in_array('32', $permissions)) { ?>
               <li><a href="<?php echo base_url() ?>reports/biotimesync">Bio-time Sync Status</a>
                 <p> - Device Synchronisation Logs by Date</p>
               </li>
               <li><a href="<?php echo base_url() ?>attendance/fingerprints">Mobile Phone Enrolled Users</a>
                 <p> - User Enrolled to Use attendance on Mobile</p>
               </li>
             <?php } ?>

           </ol>




         </div>
         <!-- /.card -->


       </section>
     </div>
     <!-- /.row (main row) -->
   </div><!-- /.container-fluid -->
 </section>
 <!-- /.content -->