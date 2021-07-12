
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
        <div class="row mb-2">
          <div class="col-sm-6">
            <!-- <h1>Fixed Layout</h1> -->
          </div>
       
        </div>
      </div>
    </section>
    <!-- /.container-fluid -->

    <!-- Main content -->
    <section class="content" style="font-size:12px;" >

      <div class="container-fluid">

        <div class="row">
          <div class="col-12" style="margin-bottom:3px;">
             <div class="card">
                <div class="card-header">
                   <h3 class="card-title"></h3>
                </div>
                   <div class="card-body">
              
                        
            <?php

              $this->load->view($module."/".$view);

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