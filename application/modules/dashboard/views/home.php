 <!-- Main content -->
 <style>
   .info-box-main {
     box-shadow: rgba(110, 68, 68, 0.2);
     background: #00838f;
     text-align: center;
     display: -ms-flexbox;
     display: flex;
     margin-bottom: 1rem;
     min-height: 90px;
     padding: .5rem;
     position: relative;
     color: #FFF;
   }
 </style>
 <section class="content">
   <div class="container-fluid">
     <!-- Main row -->
  
 
     <div class="row">
       <div class="col-xl-4 col-md-12">
         <div class="card card-">
          
           <div class="card-body">
             <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-xl" style="color:#ffc107!important;">
                 <i class="fas fa-calendar-check"></i>
               </p>
               <p class="h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">Total Staff</span>
                 <span class="info-box-number font-weight-bold" id="present"></span>
                 <small>Staff</small>
               </p>
             </div>

              <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-warning text-xl">
                 <i class="fa fa-building"></i>
               </p>
               <p class=" h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">Total Schools</span>
                 <span class="info-box-number font-weight-bold" id="departments"></span>
                 <small>Schools(s)</small>
               </p>
             </div>
         
          
          
           </div>
         </div>
       </div>
       <div class="col-xl-4 col-md-12">
         <div class="card">
          
           <div class="card-body">
             <!-- <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-warning text-xl">
                 <i class="ion ion-ios-book text-warning"></i>
               </p>
               <p class="h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">Montly Presence</span>
                 <span class="info-box-number font-weight-bold" id="rsent"></span>
                 <small>Requests</small>
               </p>
             </div> -->

                 <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-warning text-xl">
                 <i class="fa fa-building"></i>
               </p>
               <p class=" h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">Total Schools</span>
                 <span class="info-box-number font-weight-bold" id="departments"></span>
                 <small>Schools(s)</small>
               </p>
             </div>


           </div>
         </div>
       </div>
       <div class="col-xl-4 col-md-12">
         <div class="card card-">
           <div class="card-body">
             <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-success text-xl">
                 <i class="ion ion-ios-people text-info"></i>
               </p>
               <p class="h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">My Staff</span>
                 <span class="info-box-number font-weight-bold" id="mystaff"></span>
                 <small>Staff</small>
               </p>
             </div>
             <div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
               <p class="text-warning text-xl">
                 <i class="fa fa-building"></i>
               </p>
               <p class=" h6 d-flex flex-column text-right">
                 <span class="info-box-text h6 text-muted">Total Schools</span>
                 <span class="info-box-number font-weight-bold" id="departments"></span>
                 <small>Schools(s)</small>
               </p>
             </div>
           
          
           </div>
         </div>
       </div>
     </div>
     <!-- </div> endrow -->

     <div class="row">

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
             <div id="line_graph_att"></div>
           </div><!-- /.card-body -->
         </div>
         <!-- /.card -->
        </section>

       <section class="col-lg-4 connectedSortable">
         <!-- Custom tabs (Charts with tabs)-->
         <div class="card">
           <div class="card-header">

           </div>
           <div class="card-body">
             <div id="container-hours">
             </div>
           </div>
           <!-- /.card-body -->
         </div>
         <!-- Custom tabs (Charts with tabs)-->
       </section>
     </div>
     <!-- /.row (main row) -->
   </div><!-- /.container-fluid -->
 </section>
 <!-- /.content -->
 <script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
 <script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
 <script type="text/javascript">
   Highcharts.setOptions({
     colors: ['#28a745', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
   });

   $(document).ready(function() {
     knobgauge(0);
     $.ajax({
       type: 'GET',
       url: '<?php echo base_url('dashboard/dashboardData') ?>',
       dataType: "json",
       data: '',
       success: function(data) {
         $('#workers').text(data.workers);
         $('#facilities').text(data.facilities);
         $('#departments').text(data.departments);
         $('#jobs').text(data.jobs);
         $('#mystaff').text(data.mystaff);
         $('#ihris_sync').text(data.ihris_sync);
         $('#biometrics').text(data.biometrics);
         $('#roster').text(data.roster);
         $('#attendance').text(data.attendance);
         $('#biotime_last').text(data.biotime_last);
         $('#present').text(data.present);
         $('#offduty').text(data.offduty);
         $('#leave').text(data.leave);
         $('#request').text(data.request);
         $('#requesting').text(data.requesting);
         knobgauge(data.avg_hours);
         console.log(data);
       }
     });
   });


   //duty roster calendar
   var base_url = $('.base_url').html();
   $('#attcalendar').fullCalendar({
     defaultView: 'month',
     header: {
       left: 'prev, next, today',
       center: 'title',
       right: 'month, basicWeek, basicDay'
     },
     // Get all events stored in database
     eventLimit: true, // allow "more" link when too many events
     events: base_url + 'calendar/getattEvents',
     selectable: false,
     selectHelper: true,
     editable: false,
     // Mouse over
     eventMouseover: function(calEvent, jsEvent, view) {
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
  
   //duty roster graph
 
    
   <?php
    $graph = Modules::run("reports/graphData");
    ?>
   Highcharts.chart('line_graph_att', {
     chart: {
       type: 'line'
     },
     title: {
       text: 'Average Number of Employees Attending per Month - <?php echo " " . str_replace("'", " ", $_SESSION["facility_name"]) ?>'
     },
     subtitle: {
       text: ''
     },
     xAxis: {
       categories: <?php echo json_encode($graph['period']); ?>
     },
     yAxis: {
       title: {
         text: 'Staff'
       }
     },
     plotOptions: {
       line: {
         dataLabels: {
           enabled: true
         },
         enableMouseTracking: true
       }
     },
     credits: {
       enabled: false
     },
     series: [{
       name: 'Staff',
       data: <?php echo json_encode($graph['data'], JSON_NUMERIC_CHECK); ?>
     }]
   });
   // Average  Hours Gauge
   //chart options
   function knobgauge(gvalue) {
     var gaugeOptions = {
       chart: {
         type: 'solidgauge',
         height: 400,
         width: 350
       },
       pane: {
         center: ['50%', '50%'],
         size: '100%',
         startAngle: 0,
         endAngle: 360,
         background: {
           backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#EEE',
           innerRadius: '60%',
           outerRadius: '100%',
           shape: 'arc'
         }
       },
       exporting: {
         enabled: true
       },
       tooltip: {
         enabled: false
       },
       // the value axis
       yAxis: {
         stops: [
           [0.1, '#DF5353'], // red
           [0.2, '#DDDF0D'], // yellow
           [0.3, '#55BF3B'] // green
         ],
         lineWidth: 0,
         tickWidth: 0,
         minorTickInterval: null,
         tickAmount: 2,
         title: {
           y: -70
         },
         labels: {
           y: 16
         }
       },
       plotOptions: {
         solidgauge: {
           dataLabels: {
             y: 5,
             borderWidth: 0,
             useHTML: true
           }
         }
       }
     };
     //gauge
     var chartSpeed = Highcharts.chart('container-hours', Highcharts.merge(gaugeOptions, {
       title: {
         text: 'Average Monthly Hours-<?php echo " " . str_replace("'", " ", $_SESSION["facility_name"]); ?>',
       },
       yAxis: {
         min: 0,
         max: 24,
       },
       credits: {
         enabled: false
       },
       series: [{
         name: 'Hours',
         data: [parseInt(gvalue)],
         dataLabels: {
           format: '<div style="text-align:center">' +
             '<span style="font-size:12px">{y}</span><br/>' +
             '<span style="font-size:12px;opacity:0.4">Hrs</span>' +
             '</div>'
         },
         tooltip: {
           valueSuffix: ' Hours'
         }
       }]
     }))
   };
   //  // clockin method
   //  Highcharts.chart('attendance_methods', {
   //    chart: {
   //      plotBackgroundColor: null,
   //      plotBorderWidth: null,
   //      plotShadow: false,
   //      type: 'pie',
   //      height: 300,
   //      width: 300
   //    },
   //    title: {
   //      text: ''
   //    },
   //    tooltip: {
   //      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
   //    },
   //    accessibility: {
   //      point: {
   //        valueSuffix: '%'
   //      }
   //    },
   //    plotOptions: {
   //      pie: {
   //        allowPointSelect: true,
   //        cursor: 'pointer',
   //        dataLabels: {
   //          enabled: true,
   //          format: '<b>{point.name}</b>: {point.percentage:.1f} %',
   //          connectorColor: 'silver'
   //        }
   //      }
   //    },
   //    series: [{
   //      name: 'Share',
   //      data: [{
   //          name: 'Manual Form',
   //          y: 64
   //        },
   //        {
   //          name: 'BioTime',
   //          y: 11
   //        },
   //        {
   //          name: 'Mobile Phones',
   //          y: 5
   //        },
   //        {
   //          name: 'None',
   //          y: 20
   //        },
   //      ]
   //    }],
   //    credits: [{
   //      enabled: 'false',
   //    }],
   //    exporting: {
   //      buttons: {
   //        contextButton: {
   //          enabled: false
   //        }
   //      }
   //    }
   //  });
 </script>
 <script>
 </script>