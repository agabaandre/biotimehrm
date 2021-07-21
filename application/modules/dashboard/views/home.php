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
              
              <div class="card-body">
              
                <ul class="nav nav-pills" style="margin: 0 auto; margin-top:4px;">
                         <p></p>
                            
                            <?php  $colors=Modules::run('schedules/getattKey'); ?>
                            <div class="row">
                            
          
                              <?php foreach ($colors as $color) { ?>
                              <li class="nav-item">
                              <a class="btn btn-flat btnkey" style="background-color:<?php echo $color->color;  ?>;" ><?php echo $color->schedule;?></a>
                              </li>
                              <?php  }?>
                            
                              </div>
                 
          
                </ul>
                </div>
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
                <span class="info-box-text">iHRIS Health Workers</span>
                <span class="info-box-number" id="workers"></span>
              </div>
              <!-- /.info-box-content-->
            </div>
            <!-- /.info-box -->
        <div class="info-box mb-3 bg-green">
              <span class="info-box-icon"><i class="far fa-building"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Facilities</span>
                <span class="info-box-number" id="facilities" ></span>
        </div>
              <!-- /.info-box-content -->
        </div>
            <!-- /.info-box -->
         <div class="info-box mb-3 bg-danger">
              <span class="info-box-icon"><i class="fas fa-school"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Departments</span>
                <span class="info-box-number" id="departments"></span>
        </div>
              <!-- /.info-box-content -->
            </div>
            
            <!-- /.info-box -->
        <div class="info-box mb-3 bg-info">
              <span class="info-box-icon"><i class="fas fa-tasks" ></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Jobs</span>
                <span class="info-box-number" id="jobs"></span>
              </div -->
              <!-- /.info-box-content-->
        </div>
        <div class="info-box mb-3 bg-blue" >
              <span class="info-box-icon"><i class="fa fa-users"></i></span>

              <div class="info-box-content" style="color:#FFF !important;">
                <span class="info-box-text">My Facility Staff</span>
                <span class="info-box-number" id="mystaff"></span>
        </div>
              <!-- /.info-box-content -->
        </div>
        <div class="info-box mb-3 bg-success" >
              <span class="info-box-icon"><i class="fas fa-sync"></i></span>

              <div class="info-box-content" style="color:#FFF !important;">
                <span class="info-box-text">Last iHRIS Sync</span>
                <span class="info-box-number" id="ihris_sync"></span>
        </div>
              <!-- /.info-box-content -->
        </div>
        <div class="info-box mb-3 bg-orange" style="min-height:95px;">
              <span class="info-box-icon"><i class="fas fa-fingerprint" style="color:#FFF !important;"></i></span>

              <div class="info-box-content" style="color:#FFF !important;">
                <span class="info-box-text">Biotime Devices</span>
                <span class="info-box-number" id="biometrics"></span>
        </div>
              <!-- /.info-box-content -->
        </div>
      
           
       
        </div>

      
      <section class="col-lg-12 connectedSortable">
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
              <div id="line_graph_att"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

              
              </section>
            <!-- right col -->
          <section class="col-lg-6 connectedSortable">
              <!-- Custom tabs (Charts with tabs)-->
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Duty Roster Calendar

                </h3>
              
              <div class="card-body">
              
                <ul class="nav nav-pills" style="margin: 0 auto; margin-top:4px;">
                         <p></p>
                            
                            <?php  $colors=Modules::run('schedules/getrosterKey'); ?>
                            <div class="row">
                            
          
                              <?php foreach ($colors as $color) { ?>
                              <li class="nav-item">
                              <a class="btn btn-flat rbtnkey" style="background-color:<?php echo $color->color;  ?>;" data-toggle="tab"><?php echo $color->schedule;?></a>
                              </li>
                              <?php  }?>
                            
                              </div>
                 
          
                </ul>
                </div>
                <div id="dutycalendar">
               
                 
                </div>
              </div><!-- /.card-body -->
            </div>

          
          </section>

          


          
          <div class="col-md-6">
            <!-- Info Boxes Style 2 -->
            <div class="col-md-6">
                <div class="info-box mb-3 bg-warning">
                <span class="info-box-icon"><i class="fas fa-bed"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">On Leave this Month</span>
                    <span class="onleave"></span>
                  </div>
                  
                </div>
          
              <!-- /.info-box -->
              <div class="info-box mb-3 bg-success">
                <span class="info-box-icon"><i class="far fa-heart"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">On Official Request</span>
                  <span class="info-box-number">92,050</span>
                  </div>
                <!-- /.info-box-content -->
              </div>
              
             </div>
             

            
             
            


            
            <section class="col-lg-12 connectedSortable">
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
              <div id="line_graph" style="width:100%; height:100%;"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

          
          </section>

           
              <!-- /.footer -->
            </div>




    
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
<script src="<?php echo base_url()?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url()?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
<script type="text/javascript">
//get dashboard Data
// $(document).ready(function(){
//         $.ajax({
//             type:'GET',
//             url:'<?php echo base_url('dashboard/dashboardData')?>',
//             dataType: "json",
//             data:'',
//             success:function(data){
                
//                      $('#workers').text(data.workers);
//                      $('#facilities').text(data.facilities);
//                      $('#departments').text(data.departments);
//                      $('#jobs').text(data.jobs);
//                      $('#mystaff').text(data.mystaff);
//                      $('#ihris_sync').text(data.ihris_sync);
//                      $('#biometrics').text(data.biometrics);
//                     //console.log(data);
               
                
//             }
            
//         });
       
       
   
// });
 	//duty roster calendar
 	var base_url=$('.base_url').html();

 	 $('#attcalendar').fullCalendar({
        defaultView:'basicWeek',
        header: {
            left: 'prev, next, today',
            center: 'title',
             right: 'month, basicWeek, basicDay'
        },
        // Get all events stored in database
        eventLimit: true, // allow "more" link when too many events
        events:base_url+'calendar/getattEvents',
        selectable: false,
        selectHelper: true,
        editable: false,

         // Mouse over
            eventMouseover: function(calEvent, jsEvent, view){

            var tooltip = '<div class="event-tooltip">' + calEvent.duty + '</div>';
            $("body").append(tooltip);

            $(this).mouseover(function(e) {
                $(this).css('z-index', 10000);
                $('.event-tooltip').fadeIn('500');
                $('.event-tooltip').fadeTo('10', 1.9);
            }).mousemove(function(e) {
                $('.event-tooltip').css('top', e.pageY + 10);
                $('.event-tooltip').css('left', e.pageX + 20);
            });
        },
        eventMouseout: function(calEvent, jsEvent) {
            $(this).css('z-index', 8);
            $('.event-tooltip').remove();
        },
        
    });
 
//attendance calendar
 	var base_url=$('.base_url').html();

 	 $('#dutycalendar').fullCalendar({
        defaultView:'basicWeek',
        header: {
            left: 'prev, next, today',
            center: 'title',
             right: 'month, basicWeek, basicDay'
        },
        // Get all events stored in database
        eventLimit: true, // allow "more" link when too many events
        events:base_url+'calendar/getEvents',
        selectable: false,
        selectHelper: true,
        editable: false,

         // Mouse over
            eventMouseover: function(calEvent, jsEvent, view){

            var tooltip = '<div class="event-tooltip">' + calEvent.duty + '</div>';
            $("body").append(tooltip);

            $(this).mouseover(function(e) {
                $(this).css('z-index', 10000);
                $('.event-tooltip').fadeIn('500');
                $('.event-tooltip').fadeTo('10', 1.9);
            }).mousemove(function(e) {
                $('.event-tooltip').css('top', e.pageY + 10);
                $('.event-tooltip').css('left', e.pageX + 20);
            });
        },
        eventMouseout: function(calEvent, jsEvent) {
            $(this).css('z-index', 8);
            $('.event-tooltip').remove();
        },
        // H
    });
 //duty roster graph
<?php 

// $graph=Modules::run("reports/dutygraphData"); 

//  ?> 
//  Highcharts.chart('line_graph', {
//      chart: {
//          type: 'line'
//      },
//       title: {
//          text: 'Duty Roster Reporting Rate'
//      },
//      subtitle: {
//          text: ''
//      },
//      xAxis: {
//          categories: <?php echo json_encode($graph['period']); ?>
//      },
//      yAxis: {
//          title: {
//              text: 'Staff'
//          }
//      },
//      plotOptions: {
//          line: {
//              dataLabels: {
//                  enabled: true
//              },
//              enableMouseTracking: true
//          }
//      },
//      credits: {
//              enabled: false
//      },
//      series: [{
//          name: 'Staff',
//          data: <?php echo json_encode($graph['data'],JSON_NUMERIC_CHECK); ?>
//      }, {
//          name: 'Target',
//          data: <?php echo json_encode($graph['target'],JSON_NUMERIC_CHECK); ?>
//      }]
//  });
 

// <?php 

// $graph=Modules::run("reports/graphData"); 

// ?> 

// Highcharts.chart('line_graph_att', {
//     chart: {
//         type: 'line'
//     },
//     title: {
//         text: 'Attendace Reporting Rate'
//     },
//     subtitle: {
//         text: ''
//     },
//     xAxis: {
//         categories: <?php echo json_encode($graph['period']); ?>
//     },
//     yAxis: {
//         title: {
//             text: 'Staff'
//         }
//     },
//     plotOptions: {
//         line: {
//             dataLabels: {
//                 enabled: true
//             },
//             enableMouseTracking: true
//         }
//     },
//     credits: {
//             enabled: false
//     },
//     series: [{
//         name: 'Staff',
//         data: <?php echo json_encode($graph['data'],JSON_NUMERIC_CHECK); ?>
//     }, {
//         name: 'Target',
//         data: <?php echo json_encode($graph['target'],JSON_NUMERIC_CHECK); ?>
//     }]
// });

</script>



         