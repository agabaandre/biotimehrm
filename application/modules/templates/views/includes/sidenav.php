 <!-- Main Sidebar Container -->
 <aside class="main-sidebar sidebar-light-success elevation-2" >
   <!-- Brand Logo -->
   <a href="<?php echo base_url(); ?>" class="brand-link text-dark" style="background:;
   text-align:center; border-bottom: none !important;">
     <!-- <img src="../../dist/img/AdminLTELogo.png"
           alt="AdminLTE Logo"
           class="brand-image img-$decircle elevation-3"
           style="opacity: .8"> -->
     <span class="brand-text  font-weight-bold" style="text-align:center;"><?php echo $setting->title; ?></span>
   </a>
   <!-- Sidebar -->
   <div class="sidebar" style="width:100% !important; border-top: none !important;">
     <!-- Sidebar user (optional) -->
     <div class="user-panel mt-2 pb-0 mb-0" style="text-align:center; ">
       <p class="brand-image img-circle text-muted elevation" style="font-size: 11px; height:20px; font-weight:bold; margin-top:2px; opacity: .8; text-overflow: ellipsis;">
         <?php
          echo strtoupper($userdata['names']); 
          ?>
         <a href="#" class="on_off"><i class="fa fa-circle text-success"></i>Online</a>
       </p>
       <hr>
       <p class="text-muted" style="color:#FEFFFF; font-size: 10px; height:20px; font-weight:bold; margin-top:1px;">
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
           <a href="<?php echo base_url(); ?>dashboard" class="nav-link <?php echo ($this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '') ? 'active' : ''; ?>">
             <i class="fa fa-tachometer-alt"></i>
             <p>
               Main Dashboard
               </i>
             </p>
           </a>
         </li>

         <?php if (in_array('13', $permissions)) { ?>
           <li class="nav-item has-treeview <?php echo ($this->uri->segment(1) == 'employees') ? 'menu-open' : ''; ?>">
             <a href="#" class="nav-link <?php echo ($this->uri->segment(1) == 'employees') ? 'active' : ''; ?>">
               <i class="fa fa-users"></i>
               <p>
                 Staff
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>employees" class="nav-link <?php echo ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == '') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Facility Staff List</a></li>
               <li class="nav-item"><a href="<?php echo base_url() ?>employees/district_employees" class="nav-link <?php echo ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'district_employees') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   District Staff List</a></li>
               <?php if (($setting->deployment_type != "moh") && (in_array('45', $permissions))) { ?>
                 <li class="nav-item">
                   <a href="<?php echo base_url() ?>employees/createEmployee" class="nav-link <?php echo ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'createEmployee') ? 'active' : ''; ?>">
                     <i class="far fa-circle nav-icon"></i>
                     Add New Staff</a>
                 </li>
               <?php } ?>
             </ul>
           </li>
         <?php } ?>



         <?php if (in_array('14', $permissions)) { ?>
           <li class="nav-item">
             <a href="<?php echo base_url(); ?>rosta/tabular" class="nav-link <?php echo ($this->uri->segment(1) == 'rosta' && $this->uri->segment(2) == 'tabular') ? 'active' : ''; ?>">
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
             <a href="<?php echo base_url(); ?>rosta/actuals" class="nav-link <?php echo ($this->uri->segment(1) == 'rosta' && $this->uri->segment(2) == 'actuals') ? 'active' : ''; ?>">
               <i class="fa fa-clock"></i>
               <p>
                 Attendance
               </p>
             </a>
           </li>
         <?php } ?>
         <!--user perm 26-->
         <?php if (in_array('26', $permissions)) { ?>
           <li class="nav-item has-treeview <?php echo ($this->uri->segment(1) == 'requests') ? 'menu-open' : ''; ?>">
             <a href="#" class="nav-link <?php echo ($this->uri->segment(1) == 'requests') ? 'active' : ''; ?>">
               <i class="fa fa-paper-plane"></i>
               <p>
                 Requests
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>requests/newRequest" class="nav-link <?php echo ($this->uri->segment(1) == 'requests' && $this->uri->segment(2) == 'newRequest') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Submit Request</a></li>
               <li class="nav-item">
                 <a href="<?php echo base_url() ?>requests/viewMySubmittedRequests" class="nav-link <?php echo ($this->uri->segment(1) == 'requests' && $this->uri->segment(2) == 'viewMySubmittedRequests') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Requests Sent</a>
               </li>
               <?php if (in_array('25', $permissions)) { ?>
                 <li class="nav-item">
                   <a href="<?php echo base_url() ?>requests/viewRequests" class="nav-link <?php echo ($this->uri->segment(1) == 'requests' && $this->uri->segment(2) == 'viewRequests') ? 'active' : ''; ?>">
                     <i class="far fa-circle nav-icon"></i>
                     Incoming Requests</a>
                 </li>
               <?php } ?>
             </ul>
           </li>
         <?php } ?>
         <!--user perm 26-->
         <?php if (in_array('32', $permissions)) { ?>
           <li class="nav-item has-treeview <?php echo ($this->uri->segment(1) == 'biotime' || $this->uri->segment(1) == 'svariables') ? 'menu-open' : ''; ?>">
             <a href="#" class="nav-link <?php echo ($this->uri->segment(1) == 'biotime' || $this->uri->segment(1) == 'svariables') ? 'active' : ''; ?>">
               <i class="fa fa-fingerprint"></i>
               <p>
                 Biometrics
                 <i class="fas fa-angle-left right"></i>
               </p>
             </a>
             <ul class="nav nav-treeview">
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/updateTerminals" class="nav-link <?php echo ($this->uri->segment(1) == 'biotime' && $this->uri->segment(2) == 'updateTerminals') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Machines</a></li>
               <li class="nav-item">
                 <a href="<?php echo base_url() ?>biometrics/tasks" class="nav-link <?php echo ($this->uri->segment(1) == 'biometrics' && $this->uri->segment(2) == 'tasks') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Tasks</a>
               </li>
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/enrolled" class="nav-link <?php echo ($this->uri->segment(1) == 'biotime' && $this->uri->segment(2) == 'enrolled') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   Enrolled Users</a></li>
               <li class="nav-item"><a href="<?php echo base_url() ?>biotime/unenrolled" class="nav-link <?php echo ($this->uri->segment(1) == 'biotime' && $this->uri->segment(2) == 'unenrolled') ? 'active' : ''; ?>">
                   <i class="far fa-circle nav-icon"></i>
                   New Users</a></li>
            
             </ul>
           </li>
         <?php } ?>
         <?php if (in_array('17', $permissions)) { ?>
           <li class="nav-item has-treeview">
             <a href="<?php echo base_url(); ?>reports" class="nav-link <?php echo ($this->uri->segment(1) == 'reports') ? 'active' : ''; ?>">
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
           <li class="nav-item has-treeview <?php echo ($this->uri->segment(1) == 'lists' || $this->uri->segment(1) == 'svariables' || $this->uri->segment(1) == 'auth' || $this->uri->segment(1) == 'admin' || $this->uri->segment(1) == 'reasons') ? 'menu-open' : ''; ?>">
             <a href="#" class="nav-link <?php echo ($this->uri->segment(1) == 'lists' || $this->uri->segment(1) == 'svariables' || $this->uri->segment(1) == 'auth' || $this->uri->segment(1) == 'admin' || $this->uri->segment(1) == 'reasons') ? 'active' : ''; ?>">
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

                     <?php if (in_array('39', $permissions)) { ?>
                       <li class="nav-item">
                         <a href="<?php echo base_url(); ?>lists/getJobs" class="nav-link">
                           <i class="far fa-circle nav-icon"></i>
                           <p>Jobs</p>
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
           <a href="<?php echo base_url(); ?>"  class="passchange nav-link dropdown-toggle" data-toggle="modal" role="button" data-target="#changepassword">
             <i class="fa fa-lock"></i>
             <p>
               Change Password
              
             </p>
           </a>
         </li>
         
         <!-- Logout Button -->
         <li class="nav-item sidebar-logout-item">
           <a href="<?php echo base_url(); ?>auth/logout" class="nav-link sidebar-logout-link">
             <i class="fas fa-sign-out-alt"></i>
             <p>
               Logout
             </p>
           </a>
         </li>
       </ul>
     </nav>
     <!-- /.sidebar-menu -->
   </div>
   <!-- /.sidebar -->
 </aside>

<!-- Floating Logout Button Container -->
<div class="sidebar-logout-float">
  <a href="<?php echo base_url(); ?>auth/logout" class="sidebar-logout-float-link">
    <i class="fas fa-sign-out-alt"></i>
    <span>Logout</span>
  </a>
</div>

<style>
/* Sidebar Logout Button Styling */
.sidebar-logout-item {
  margin-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 15px;
}

/* Active Navigation Styling */
.nav-sidebar .nav-link.active,
.nav-sidebar .nav-item.has-treeview.menu-open > .nav-link {
  background-color: rgba(0, 86, 98, 0.1) !important;
  color: #005662 !important;
  border-radius: 8px !important;
  margin: 0 10px !important;
  padding: 10px 15px !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 15px rgba(0, 86, 98, 0.3) !important;
}

.nav-sidebar .nav-link.active:hover,
.nav-sidebar .nav-item.has-treeview.menu-open > .nav-link:hover {
  background-color: rgba(0, 86, 98, 0.1) !important;
  color: #005662 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(0, 86, 98, 0.4) !important;
}

.nav-sidebar .nav-link.active i,
.nav-sidebar .nav-item.has-treeview.menu-open > .nav-link i {
  color: #005662 !important;
}

.nav-sidebar .nav-link.active p,
.nav-sidebar .nav-item.has-treeview.menu-open > .nav-link p {
  color: #005662 !important;
}

/* Active submenu items */
.nav-sidebar .nav-treeview .nav-link.active {
  background-color: rgba(0, 86, 98, 0.1) !important;
  color: #005662 !important;
  border-radius: 6px !important;
  margin: 2px 15px !important;
  padding: 8px 12px !important;
}

.nav-sidebar .nav-treeview .nav-link.active:hover {
  background-color: rgba(0, 86, 98, 0.1) !important;
  color: #005662 !important;
}

.nav-sidebar .nav-treeview .nav-link.active i {
  color: #005662 !important;
}

/* Hover effects for all nav links */
.nav-sidebar .nav-link:hover {
  background-color: rgba(0, 86, 98, 0.1) !important;
  color: #005662 !important;
  border-radius: 8px !important;
  margin: 0 10px !important;
  padding: 10px 15px !important;
  transition: all 0.3s ease !important;
}

.nav-sidebar .nav-link:hover i,
.nav-sidebar .nav-link:hover p {
  color: #005662 !important;
}

/* Treeview arrow color for active items */
.nav-sidebar .nav-item.has-treeview.menu-open > .nav-link .right {
  color: #fff !important;
}

.sidebar-logout-link {
  background: #389b8c !important;
  color: #ffffff !important;
  border-radius: 5px !important;
  margin: 0 10px !important;
  padding: 12px 15px !important;
  transition: all 0.3s ease !important;
  border: 2px solid rgba(255, 255, 255, 0.1) !important;
  text-align: center !important;
  font-weight: 600 !important;
  box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
}

.sidebar-logout-link:hover {
  background:#389b8c !important;
  color: #ffffff !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(57, 152, 149, 0.4) !important;

}

.sidebar-logout-link i {
  color: #ffffff !important;
  margin-right: 8px !important;
  font-size: 16px !important;
}

.sidebar-logout-link p {
  color: #ffffff !important;
  margin: 0 !important;
  font-size: 14px !important;
  font-weight: 400 !important;
}

/* Hover effect for the logout button */
.sidebar-logout-link:hover i,
.sidebar-logout-link:hover p {
  color: #ffffff !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .sidebar-logout-link {
    margin: 0 5px !important;
    padding: 10px 12px !important;
  }
  
  .sidebar-logout-link p {
    font-size: 13px !important;
  }
}

/* Floating Logout Button - Always at bottom */
.sidebar-logout-float {
  position: fixed;
  bottom: 20px;
  left: 20px;
  z-index: 1000;
  width: 180px;
  max-width: calc(100vw - 40px);
}

.sidebar-logout-float-link {
  display: flex;
  align-items: center;
  justify-content: center;
  background:#389b8c !important;
  color: #ffffff !important;
  text-decoration: none !important;
  padding: 10px 16px !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 15px rgba(0, 86, 98, 0.3) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  transition: all 0.3s ease !important;
  font-weight: 500 !important;
}

.sidebar-logout-float-link:hover {
  background: linear-gradient(135deg,rgb(23, 184, 165) 0%,#389b8c 100%) !important;
  color: #ffffff !important;
  transform: translateY(-3px) !important;
  box-shadow: 0 8px 30pxrgb(60, 171, 154) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  text-decoration: none !important;
}

.sidebar-logout-float-link i {
  color: #ffffff !important;
  margin-right: 10px !important;
  font-size: 18px !important;
}

.sidebar-logout-float-link span {
  color: #ffffff !important;
  font-size: 15px !important;
  font-weight: 600 !important;
}

/* Add a subtle accent using your secondary color */
.sidebar-logout-float-link:hover i {
  color: #389b8c !important;
  transform: scale(1.1);
  transition: all 0.3s ease;
}

/* Hide the original logout button in sidebar menu */
.sidebar-logout-item {
  display: none !important;
}

/* Responsive adjustments for floating button */
@media (max-width: 768px) {
  .sidebar-logout-float {
    bottom: 15px;
    left: 15px;
    width: calc(100vw - 30px);
    max-width: 250px;
  }
  
  .sidebar-logout-float-link {
    padding: 12px 16px !important;
  }
  
  .sidebar-logout-float-link i {
    font-size: 16px !important;
  }
  
  .sidebar-logout-float-link span {
    font-size: 14px !important;
  }
}

/* Ensure the button is above other content */
.sidebar-logout-float {
  z-index: 9999 !important;
}
</style>
