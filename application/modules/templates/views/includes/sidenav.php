 <!-- Main Sidebar Container -->
 <aside class="main-sidebar sidebar-dark-primary elevation-2">
   <!-- Brand Logo -->
   <a href="<?php echo base_url(); ?>" class="brand-link" style="background: #005662;
    color: #FFFFFF; text-align:center;">
     <!-- <img src="../../dist/img/AdminLTELogo.png"
           alt="AdminLTE Logo"
           class="brand-image img-circle elevation-3"
           style="opacity: .8"> -->
     <span class="brand-text  font-weight-bold" style="text-align:center;"><?php echo $setting->title; ?></span>
   </a>
   <!-- Sidebar -->
   <div class="sidebar  sido" style="width:100% !important;">
     <!-- Sidebar user (optional) -->
     <div class="user-panel mt-2 pb-0 mb-0" style="text-align:center; line-height:0.2cm;">
       <p class="brand-image img-circle elevation" style="color:#FEFFFF; font-size: 11px; height:20px; font-weight:bold; margin-top:2px; opacity: .8;">
         <?php
          //echo strtoupper($userdata['names']); 
          ?>
         <a href="#" class="on_off"><i class="fa fa-circle text-success"></i>Online</a>
       </p>
       <hr>
       <p style="color:#FEFFFF; font-size: 10px; height:20px; font-weight:bold; margin-top:1px;">
         <?php
          echo $period = "PERIOD:" . $userdata['month'] . '-' . $userdata['year'] . '<br>';
          // echo date('Y-m-d H:i:s');
          ?>
       </p>

     </div>
     <!-- Sidebar Menu -->
     <nav class="mt-2" style="overflow:hidden; font-size:14px;">
       <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
         <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
         <li class="nav-item has-treeview">
           <a href="<?php echo base_url(); ?>" class="nav-link">
             <i class="fa fa-tachometer-alt"></i>
             <p>
               Main Dashboard
               </i>
             </p>
           </a>
         </li>

         <?php if (in_array('13', $permissions)) { ?>
           <li class="nav-item has-treeview ">
             <a href="#" class="nav-link">
               <i class="fa fa-users"></i>
               <p>
                 Staff
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>employees" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Facility Staff List</a></li>
               <li class="nav-item"><a href="<?php echo base_url() ?>district_employees" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   District Staff List</a></li>
               <li class="nav-item">
                 <a href="<?php echo base_url() ?>employees/createEmployee" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Add New Staff</a>
               </li>
             </ul>
           </li>
         <?php } ?>



         <?php if (in_array('14', $permissions)) { ?>
           <li class="nav-item">
             <a href="<?php echo base_url(); ?>rosta/tabular" class="nav-link">
               <i class="fa fa-calendar"></i>
               <p>
                 Duty Roster
               </p>
             </a>
           </li>
         <?php } ?>
         <!-- <?php if (in_array('14', $permissions)) { ?>
          <li class="nav-item">
            <a href="<?php echo base_url(); ?>rosta/leaveRoster" class="nav-link">
              <i class="fas fa-sleigh"></i>
              <p>
                Leave Roster
              </p>
            </a>
          </li>
          <?php } ?> -->
         <?php if (in_array('19', $permissions)) { ?>
           <li class="nav-item">
             <a href="<?php echo base_url(); ?>rosta/actuals" class="nav-link">
               <i class="fa fa-clock"></i>
               <p>
                 Attendance
               </p>
             </a>
           </li>
         <?php } ?>
         <!--user perm 26-->
         <?php if (in_array('26', $permissions)) { ?>
           <li class="nav-item has-treeview ">
             <a href="#" class="nav-link">
               <i class="fa fa-paper-plane"></i>
               <p>
                 Requests
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>requests/newRequest" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Submit Request</a></li>
               <li class="nav-item">
                 <a href="<?php echo base_url() ?>requests/viewMySubmittedRequests" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Requests Sent</a>
               </li>
               <?php if (in_array('25', $permissions)) { ?>
                 <li class="nav-item">
                   <a href="<?php echo base_url() ?>requests/viewRequests" class="nav-link">
                     <i class="far fa-circle nav-icon"></i>
                     Incoming Requests</a>
                 </li>
               <?php } ?>
             </ul>
           </li>
         <?php } ?>
         <!--user perm 26-->
         <?php if (in_array('32', $permissions)) { ?>
           <li class="nav-item has-treeview ">
             <a href="#" class="nav-link">
               <i class="fa fa-fingerprint"></i>
               <p>
                 Biometrics
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/updateTerminals" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Machines</a></li>
               <li class="nav-item">
                 <a href="<?php echo base_url() ?>biotime/tasks" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Tasks</a>
               </li>
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/enrolled" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Enrolled Users</a></li>
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/unenrolled" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   New Users</a></li>
               <li class="nav-item"><a href="<?php echo base_url() ?>svariables/logs" class="nav-link">
                   <i class="far fa-circle nav-icon"></i>
                   Read Logs</a></li>
             </ul>
           </li>
         <?php } ?>
         <?php if (in_array('17', $permissions)) { ?>
           <li class="nav-item has-treeview">
             <a href="<?php echo base_url(); ?>reports" class="nav-link">
               <i class="fa fa-th"></i>
               <p>
                 Reports
                 </i>
               </p>
             </a>
           </li>
         <?php } ?>
         <!--user perm 14-->
         <?php if (in_array('35', $permissions)) { ?>
           <li class="nav-item has-treeview ">
             <a href="#" class="nav-link">
               <i class="fa fa-cog"></i>
               <p>
                 Settings
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">

               <!-- employee form lists-->
               <?php if (in_array('39', $permissions)) { ?>
                 <li class="nav-item">
                   <a href="#" class="nav-link">
                     <i class="far fa-circle nav-icon"></i>
                     <p>
                       Employee Form Lists
                       <i class="right fas fa-angle-left"></i>
                     </p>
                   </a>
                   <ul class="nav nav-treeview" style="display: none;">
                     <?php if (in_array('39', $permissions)) { ?>
                       <li class="nav-item">
                         <a href="<?php echo base_url(); ?>lists/getFacilities" class="nav-link">
                           <i class="far fa-circle nav-icon"></i>
                           <p>Facilities</p>
                         </a>
                       </li>
                     <?php } ?>

                     <?php if (in_array('39', $permissions)) { ?>
                       <li class="nav-item">
                         <a href="<?php echo base_url(); ?>lists/getDistricts" class="nav-link">
                           <i class="far fa-circle nav-icon"></i>
                           <p>Districts</p>
                         </a>
                       </li>
                     <?php } ?>

                     <?php if (in_array('39', $permissions)) { ?>
                       <li class="nav-item">
                         <a href="<?php echo base_url(); ?>lists/getCadres" class="nav-link">
                           <i class="far fa-circle nav-icon"></i>
                           <p>Cadres</p>
                         </a>
                       </li>
                     <?php } ?>
                     <?php if (in_array('44', $permissions)) { ?>
                       <li class="nav-item">
                         <a href="<?php echo base_url(); ?>svariables" class="nav-link">
                           <i class="far fa-circle nav-icon"></i>
                           <p>Constants & Variables</p>
                         </a>
                       </li>
                     <?php } ?>
                   </ul>
                 </li>
               <?php } ?>

               <!-- end employee form lists -->

               <!-- User Management -->
               <?php if (in_array('15', $permissions)) { ?>
                 <li class="nav-item">
                   <a href="#" class="nav-link">
                     <i class="far fa-circle nav-icon"></i>
                     <p>
                       User Management
                       <i class="right fas fa-angle-left"></i>
                     </p>
                   </a>
                   <ul class="nav nav-treeview" style="display: none;">
                     <li class="nav-item">
                       <a href="<?php echo base_url(); ?>auth/users" class="nav-link">
                         <i class="far fa-circle nav-icon"></i>
                         <p>Manage User</p>
                       </a>
                     </li>
                     <li class="nav-item">
                       <a href="<?php echo base_url(); ?>admin/groups" class="nav-link">
                         <i class="far fa-circle nav-icon"></i>
                         <p>Group Permissions</p>
                       </a>
                     </li>
                     <li class="nav-item">
                       <a href="<?php echo base_url(); ?>admin/showLogs" class="nav-link">
                         <i class="far fa-circle nav-icon"></i>
                         <p>Activity Logs</p>
                       </a>
                     </li>
                   </ul>
                 </li>
               <?php } ?>

               <!-- end user management -->


               <!-- Schedule Management -->
               <?php if (in_array('43', $permissions)) { ?>
                 <li class="nav-item">
                   <a href="#" class="nav-link">
                     <i class="far fa-circle nav-icon"></i>
                     <p>
                       Schedule Management
                       <i class="right fas fa-angle-left"></i>
                     </p>
                   </a>
                   <ul class="nav nav-treeview" style="display: none;">
                     <li class="nav-item">
                       <a href="<?php echo base_url(); ?>schedules/all_schedules" class="nav-link">
                         <i class="far fa-circle nav-icon"></i>
                         <p>Schedule Types</p>
                       </a>
                     </li>
                     <li class="nav-item">
                       <a href="<?php echo base_url(); ?>reasons/addReason" class="nav-link">
                         <i class="far fa-circle nav-icon"></i>
                         <p>Out of Station Reasons</p>
                       </a>
                     </li>

                   </ul>
                 </li>

               <?php } ?>

               <!-- end schedule management -->

             </ul>
           </li>
         <?php } ?>
         <li class="nav-item has-treeview">
           <a href="<?php echo base_url(); ?>" class="nav-link" class="passchange nav-link dropdown-toggle" data-toggle="modal" role="button" data-target="#changepassword">
             <i class="fa fa-lock"></i>
             <p>
               Change Password
               </i>
             </p>
           </a>
         </li>
       </ul>
     </nav>
     <!-- /.sidebar-menu -->
   </div>
   <!-- /.sidebar -->
 </aside>