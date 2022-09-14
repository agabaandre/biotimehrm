<?php
date_default_timezone_set('Africa/Kampala');
require_once("includes/header.php");
require_once("includes/navtop.php");
require_once("includes/sidenav.php");
//db connection
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <!-- <h1>Fixed Layout</h1> -->
        </div>
      </div>
    </div>
  </section>
  <!-- /.container-fluid -->
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid" style="font-size:12px; min-height:940px; margin-top:40px;">
      <div class="row">
        <div class="col-12" style="margin-bottom:3px;">
          <div class="card">
            <div class="">
            </div>
            <div class="card-body">
              <?php
              $this->load->view($module . "/" . $view);
              ?>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
            </div>
            <!-- /.card-footer-->
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
require_once("includes/footer.php");
?>