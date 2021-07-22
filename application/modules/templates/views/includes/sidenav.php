
 <!-- Main Sidebar Container -->
 
 
 <aside class="main-sidebar sidebar-dark-primary elevation-4" >
    <!-- Brand Logo -->
    <a href="<?php echo base_url();?>" class="brand-link" style="background: linear-gradient( 135deg, rgb(56 54 54) 0%, rgb(27 131 173) 100%);
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
      
      <div class="user-panel mt-3 pb-3 mb-3" style="text-align:center; line-height:0.2cm;">

      <p class="brand-image img-circle elevation-2" style="color:#FEFFFF; font-size: 11px; height:20px; font-weight:bold; margin-top:2px; opacity: .8;">

      <?php  

          echo strtoupper($userdata['names']); 


          ?>
         
          

      </p>
          <hr>
        <p style="color:#FEFFFF; font-size: 10px; height:20px; font-weight:bold; margin-top:1px;">
          <?php 
          echo $period="PERIOD:". $userdata['month'].'-'.$userdata['year'];
        ?>
        </p>
        <div class="image">
          <p ><img src="<?php echo base_url(); ?>assets/img/user.jpg" class="img-circle elevation-2" alt="User Image" style="width:35px; height:35px;"></p>
        </div>
      
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
       
          <?php if(in_array('15', $permissions)){ ?>
         <li class="nav-item">
            <a href="<?php echo base_url(); ?>employees" class="nav-link">
              <i class="fa fa-users"></i>
              <p>
                  Staff
              </p>
            </a>
          </li>
          <?php } ?>

          <?php if(in_array('14', $permissions)){ ?>
          <li class="nav-item">
            <a href="<?php echo base_url(); ?>rosta/tabular" class="nav-link">
              <i class="fa fa-calendar"></i>
              <p>
                Duty Roster
                
              </p>
            </a>
          </li>
          <?php } ?>
          <!-- <?php if(in_array('14', $permissions)){ ?>
          <li class="nav-item">
            <a href="<?php echo base_url(); ?>rosta/leaveRoster" class="nav-link">
              <i class="fas fa-sleigh"></i>
              <p>
                Leave Roster
                
              </p>
            </a>
          </li>
          <?php } ?> -->


          <?php if(in_array('14', $permissions)){ ?>
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
           <?php if(in_array('26', $permissions)){ ?>
            <li class="nav-item has-treeview ">
            <a href="#" class="nav-link">
              <i class="fa fa-paper-plane"></i>
              <p>
                Requests
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
                               <li class="nav-item"><a href="<?php echo base_url()?>requests/newRequest" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Submit Request</a></li>
                                <li class="nav-item">
                                <a href="<?php echo base_url()?>requests/viewMySubmittedRequests" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Requests Sent</a>
                                </li>

                                <?php if(in_array('25', $permissions)){ ?>
                                <li class="nav-item">
                                    <a href="<?php echo base_url()?>requests/viewRequests" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    Incoming Requests</a>
                                </li>
                                <?php } ?>


            </ul>
            </li>
            <?php } ?>
              <!--user perm 26-->
           <?php if(in_array('26', $permissions)){ ?>
            <li class="nav-item has-treeview ">
            <a href="#" class="nav-link">
              <i class="fa fa-fingerprint"></i>
              <p>
                Biotime
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
                               <li class="nav-item"><a href="<?php echo base_url()?>biotime/updateTerminals" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Machines</a></li>
                                <li class="nav-item">
                                <a href="<?php echo base_url()?>biotime/tasks" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Tasks</a>
                                </li>



            </ul>
            </li>
            <?php } ?>
                <!--user perm 26-->
           <?php if(in_array('26', $permissions)){ ?>
            <li class="nav-item has-treeview ">
            <a href="#" class="nav-link">
              <i class="fa fa-mobile"></i>
              <p>
                Mobile Phones
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
                               <li class="nav-item"><a href="<?php echo base_url()?>biotime/updateTerminals" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Enrolled Users</a></li>
                             



            </ul>
            </li>
            <?php } ?>

        

          <li class="nav-item has-treeview">
            <a href="<?php echo base_url(); ?>reports" class="nav-link">
              <i class="fa fa-th"></i>
              <p>
               Reports
                </i>
              </p>
            </a>
          </li>

          <!--user perm 14-->
 <?php if(in_array('15', $permissions)){ ?>
          <li class="nav-item has-treeview ">
            <a href="#" class="nav-link">
              <i class="fa fa-cog"></i>
              <p>
                Settings
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
                 <li class="nav-item">
                <a href="<?php echo base_url();?>svariables"  class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Constants & Variables</p>
                </a>
                  </li>
                <li class="nav-item">

   
                <a href="<?php echo base_url();?>auth/users" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Manage User</p>
                </a>
                 </li>
                 <li class="nav-item">
                <a href="<?php echo base_url();?>admin/groups" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>User Groups</p>
                </a>
                </li>
                <li class="nav-item">
                <a href="<?php echo base_url();?>admin/showLogs" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Activity Logs</p>
                </a>
                </li>
                <li class="nav-item">
                <a href="<?php echo base_url();?>schedules/all_schedules" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Schedule Types</p>
                </a>
                </li>
                <li class="nav-item">
                <a href="<?php echo base_url();?>reasons/addReason"class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Out of Station Reasons</p>
                </a>
                </li>
                
                
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
  <script>

function getFacs(val) {
   
   $.ajax({          
           method: "GET",
           url: "<?php echo base_url(); ?>departments/get_facilities",
           data:'dist_data='+val,
           success: function(data){
               //alert(data);
               $("#facility").html(data);
           }
   });
}

function getDeps(val) {
   
   $.ajax({          
           method: "GET",
           url: "<?php echo base_url(); ?>departments/get_departments",
           data:'fac_data='+val,
           success: function(data){
               //alert(data);
               $("#depart").html(data);
           }
   });
}
function getDivisions(val) {
   
    $.ajax({          
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_divisions",
            data:'depart_data='+val,
            success: function(data){
                // alert(data);
                $("#division").html(data);
            }
    });
}


function getUnits(val) {
   
    $.ajax({          
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_units",
            data:'division='+val,
            success: function(data){
                //alert(data);
                $("#unit").html(data);
            }
    });
}

</script>