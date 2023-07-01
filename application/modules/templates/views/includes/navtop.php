<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark" style="background: #948c40;
    color:inherit; text-align:center;">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <div class="header-title" style="color:#FFF; margin-top:5px;">
        <?php if (!empty($uptitle)) {
          echo urldecode($uptitle);
        } ?>
        </div>
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
  <?php //print_r($this->session->userdata())
  ?>
  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item" style="position:relative; float:left; margin-right:50px; margin-top:7px; font-size:13px; color:#FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
      <a>
        <?php if (isset($_SESSION['district'])) {
          echo  $_SESSION['district'] . ' - ';
        } ?> <b><span style="color: #FFFFFF;"></span></b> <?php if (isset($_SESSION['facility_name'])) {
                                                            echo $_SESSION['facility_name'];
                                                          } ?>
      </a>
    </li>
    <?php if (in_array('13', $permissions)) { ?>
      <li class="nav-item" style="margin-right:2px;">
        <a class="btn btn-default btn-outline" data-toggle="modal" data-target="#switch" style="border-radius:14px;">
          <i class="fas fa-toggle-on"></i> <b class="hidden-mobile">Change Facility</b>
        </a>
      </li>
    <?php } ?>
    <li class="nav-item dropdown show" style="margin-right:20px; margin-left:20px; animation: growDown 300ms ease-in-out forwards;">
      <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
        <span style="clear:both; color:#FFFFFF !important;">
          <?php
          echo $userdata['names'];
          // print_r($userdata); 
          ?>
        </span>
        <span>
          <img src="<?php echo base_url(); ?>assets/img/user.jpg" alt="" class="img-circle elevation-2" style="max-width:20px;" /></span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#profile"><i class="fa fa-user"></i> Profile</a>
        <div class="dropdown-divider"></div>
        <a href="<?php echo base_url(); ?>auth/logout" class="dropdown-item"><i class="fa fa-arrow-left"></i> Logout</a>
    </li>
    </div>
  </ul>
</nav>
<!-- /.navbar -->