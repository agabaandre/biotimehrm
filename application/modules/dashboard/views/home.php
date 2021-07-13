 <!-- Main content -->
 <section class="content">
      <div class="container-fluid">
        <!-- Main row -->

        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>
              <span class="base_url" style="display: none;" ><?php echo base_url(); ?></span>
              <div class="info-box-content">
                <span class="info-box-text">Completed Roster</span>
                <span class="info-box-number">
                  10
                  <small>%</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Completed Attendance</span>
                <span class="info-box-number">41,410</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Biometric Devices</span>
                <span class="info-box-number">760</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Active Users</span>
                <span class="info-box-number">2,000</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>

        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">My Staff</span>
                <span class="info-box-number">
                  10
                  <small>%</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Last iHRIS Sync</span>
                <span class="info-box-number">41,410</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Last BioTime Sync</span>
                <span class="info-box-number">760</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Active Districts</span>
                <span class="info-box-number">2,000</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>

  

        <div class="row">
        
              <!-- Left col -->
            <section class="col-lg-9 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Attendance Calendar

                </h3>
                <div class="row">
                    
               
              
              </div><!-- /.card-header -->
              <div class="card-body">
              <div class="row" style="text-align:center; font-weight:bold;"><p>KEY</p></div>
                <ul class="nav nav-pills" style="margin: 0auto;">
                            
                            <?php  $colors=Modules::run('schedules/getattKey'); ?>
                            <div class="row">
                            
          
                              <?php foreach ($colors as $color) { ?>
                              <li class="nav-item">
                              <a class="btn btn-flat btnkey" style="background-color:<?php echo $color->color;  ?>;" data-toggle="tab"><?php echo $color->schedule;?></a>
                              </li>
                              <?php  }?>
                            
                              </div>
                              </div>
          
                </ul>
                <div id="attcalendar">
               
                 
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->


            <!-- calender key -->
          </section>

          

        <div class="col-md-3">
            <!-- Info Boxes Style 2 -->
            <div class="info-box mb-3 bg-blue">
              <span class="info-box-icon"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Health Workers</span>
                <span class="info-box-number"><?php echo $districts ?></span>
              </div>
              <!-- /.info-box-content-->
            </div>
            <!-- /.info-box -->
        <div class="info-box mb-3 bg-green">
              <span class="info-box-icon"><i class="far fa-building"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Facilities</span>
                <span class="info-box-number"><?php echo $facilities ?></span>
        </div>
              <!-- /.info-box-content -->
        </div>
            <!-- /.info-box -->
         <div class="info-box mb-3 bg-danger">
              <span class="info-box-icon"><i class="fas fa-school"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Departments</span>
                <span class="info-box-number"><?php echo $departments ?></span>
        </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        <div class="info-box mb-3 bg-info">
              <span class="info-box-icon"><i class="fas fa-mobile-alt" ></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Jobs</span>
                <span class="info-box-number"><?php echo $jobs ?></span>
              </div -->
              <!-- /.info-box-content-->
        </div>
            <!-- /.info-box -->

           
              <!-- /.footer -->
        </div>
    




          <!-- right col -->
          <section class="col-lg-9 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Duty Roster Calendar
                </h3>
                <div class="card-tools">
                <div class="row" style="text-align:center; font-weight:bold;"><p>KEY</p></div>
                <ul class="nav nav-pills" style="margin: 0auto;">
                            
                            <?php  $colors=Modules::run('schedules/rostaKey'); ?>
                            <div class="row">
                            
          
                              <?php foreach ($colors as $color) { ?>
                              <li class="nav-item">
                              <a class="btn btn-flat btnkey" style="background-color:<?php echo $color->color;  ?>;" data-toggle="tab"><?php echo $color->schedule;?></a>
                              </li>
                              <?php  }?>
                            
                              </div>
                              </div>
          
                </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                   <div id='dutycalendar'></div>  
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

          
          </section>


          
          <div class="col-md-3">
            <!-- Info Boxes Style 2 -->
            <div class="info-box mb-3 bg-warning">
              <!--span class="info-box-icon"><i class="fas fa-tag"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Inventory</span>
                <span class="info-box-number">5,200</span>
              </div -->
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-success">
              <!--span class="info-box-icon"><i class="far fa-heart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Mentions</span>
                <span class="info-box-number">92,050</span>
              </div -->
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-danger">
              <!--span class="info-box-icon"><i class="fas fa-cloud-download-alt"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Downloads</span>
                <span class="info-box-number">114,381</span>
              </div -->
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-info">
              <!--span class="info-box-icon"><i class="far fa-comment"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Direct Messages</span>
                <span class="info-box-number">163,921</span>
              </div -->
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->

           
              <!-- /.footer -->
            </div>


          <section class="col-lg-4 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
               
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
              <div id="monthlyhours"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

          
          </section>


          <section class="col-lg-8 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
              
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
              <div id="clockin-container" style="width:90%; height:90%;"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

          
          </section>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
<script src="<?php echo base_url()?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/fullcalendar/fullcalendar.min.js"></script>

    