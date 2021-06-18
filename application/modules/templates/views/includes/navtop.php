<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark" style="text-align:center; background-color: rgb(123, 159, 14);" >
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
      <div class="header-title" style="color:#FFF; margin-top:5px;">
              <?php  if(!empty($uptitle)) { echo urldecode( $uptitle); } ?>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3" style="display:none;">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>
    <?php //print_r($this->session->userdata())?>
    
    <!-- Right navbar links -->
   
    <ul class="navbar-nav ml-auto">

      <li class="nav-item" style="position:relative; float:left; margin-right:50px; margin-top:7px; font-size:13px; color:#FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 420px;">
                                
              
                                <a>
                                    <?php if(isset($_SESSION['district'])){ echo  $_SESSION['district']; }?> <b><span style="color: #FFFFFF;"></span></b>  <?php if(isset($_SESSION['facility_name'])){ echo " "."-"." ".$_SESSION['facility_name'];} ?> <b><span style="color: #FFFFFF;"></span></b> <?php if(isset($_SESSION['department'])){ echo " "."-"." ".$_SESSION['department']; } ?>  
                                </a>  
   
                            
       </li>

        <?php if(in_array('13', $permissions)){ ?>
        <li class="nav-item" style="margin-right:2px;">
         <a class="btn btn-default btn-outline" data-toggle="modal" data-target="#switch" style="border-radius:14px;">
         <i class="fas fa-toggle-on"></i> <b class="hidden-mobile">Change Facility</b>
        </a> 
      </li>
      <?php }?>

    
      <!-- 
      
      <li class="nav-item" style="margin-right:2px;">
         <a class="nav-link btn btn-sm btn-primary"  target="_blank" href="http://hris.health.go.ug/districts" style="color:#FFF;">
         <i class="fas fa-flag"></i><b class="hidden-mobile"> National Manage</b>
        </a> 
      </li>
      <li class="nav-item" style="margin-right:2px;">
         <a class="nav-link btn btn-sm btn-primary"  target="_blank" href="http://hris.health.go.ug/hrattendance" style="color:#FFF;">
         <i class="fas fa-clock"></i><b class="hidden-mobile"> Duty Roster</b>
        </a> 
      </li>
    
      <li class="nav-item dropdown">
        <a class="nav-link btn btn-sm btn-primary" data-toggle="dropdown" href="#" style="color:#FFF;">
        <i class="fas fa-globe"></i><b class="hidden-mobile">IHRIS Demos</b>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-divider"></div>
          <a href="http://hris.health.go.ug/demo_manage" target="_blank" class="dropdown-item">
            <i class="fas fa-globe"></i>iHRIS Manage Demo
          </a>
          <div class="dropdown-divider"></div>
          <a href="http://hris.health.go.ug/train_demo/login" target="_blank" class="dropdown-item">
            <i class="fas fa-globe"></i> iHRIS Train
          </a>
          <div class="dropdown-divider"></div>
          <a href="http://hris.health.go.ug/iHRIS/releases/4.1/DES_demo/login" target="_blank" class="dropdown-item">
            <i class="fas fa-globe"></i> DES Demo
          </a>
          <div class="dropdown-divider"></div>
          <a href="//hris.health.go.ug/iHRIS/dev/demo-chwr" target="_blank" class="dropdown-item">
            <i class="fas fa-globe"></i> CHWR Demo
          </a>
          <div class="dropdown-divider"></div>
          <a href="http://hris.health.go.ug/dutyrosterdemo" target="_blank" class="dropdown-item">
            <i class="fas fa-globe"></i> Duty Roster/ Attend
          </a>
         
      </li> -->
      <li class="nav-item sidebar-header">
                                <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
                                    <span style="clear:both; color:#FFFFFF !important;">

                                    <?php $userdata=$this->session->get_userdata(); 

                                         echo $userdata['names']; 

                                       // print_r($userdata); 

                                    ?>
                         
                                   </span>
                                    <span>
                                    <img src="<?php echo base_url(); ?>assets/img/user.jpg" alt="" class="img-circle elevation-2" style="max-width:20px;"/></span>
                                 </a>
                                 <ul role="menu" class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

                                   <div class="dropdown-divider"></div>
                                    <li><a href="#" data-toggle="modal" data-target="#profile"><span class="dropdown-item"></span><i class="fa fa-user"></i> My Profile</a>
                                    </li>
                                    
                                    <div class="dropdown-divider"></div>
                                    <li><a href="<?php echo base_url(); ?>auth/logout"><span class="dropdown-item"></span><i class="fa fa-sign-out"></i> Log Out</a>
                                    </li>
                                </ul>
                            </li>  
     
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
      
    </ul>
  </nav>
  <!-- /.navbar -->
