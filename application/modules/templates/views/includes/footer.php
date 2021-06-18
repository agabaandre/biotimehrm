</div>
  <!-- /.content-wrapper -->

  <footer class="main-footer" style="text-align:center; color:#FFFFFF; background-color: rgb(123, 159, 14);" >
                <div class="col-lg-12">
                    <div class="footer-copy-right">
                    <!-- <img src="https://upload.wikimedia.org/wikipedia/commons/1/17/USAID-Identity.svg" style="width:180px; height:50px;">
                          <a href="http://health.go.ug" target="_blank"> <img src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Coat_of_arms_of_Uganda.svg" style="width:80px; height:50px;"> </a> -->
                    <p >&copy; <?php  echo date('Y'); ?>, Ministry of Health -Uganda. <strong>All Rights Reserved</strong></p>
                    </div>
                </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<!-- Bootstrap 4 -->
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url() ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url(); ?>assets/dist/js/adminlte.min.js"></script>

<script src="<?php echo base_url(); ?>assets/dist/js/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?php echo base_url(); ?>assets/dist/js/demo.js"></script>

<script src="<?php echo base_url()?>assets/dist/js/demo.js"></script>
<!-- fullCalendar 2.2.5 -->
<script src="<?php echo base_url()?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url()?>assets/plugins/fullcalendar/main.min.js"></script>
<script src="<?php echo base_url()?>assets/plugins/fullcalendar-daygrid/main.min.js"></script>
<script src="<?php echo base_url()?>assets/plugins/fullcalendar-timegrid/main.min.js"></script>
<script src="<?php echo base_url()?>assets/plugins/fullcalendar-interaction/main.min.js"></script>
<script src="<?php echo base_url()?>assets/plugins/fullcalendar-bootstrap/main.min.js"></script>


 <script src="<?php echo base_url(); ?>assets/js/notify.min.js"></script>
 <script>
    $(function () {
    
  
    $('#mytab2').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#buttons .col-md-6:eq(0)');
  });
</script>
   <script src="<?php echo base_url(); ?>assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo base_url(); ?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- DataTables  & Plugins -->
<script src="<?php echo base_url(); ?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/jszip/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

 



<!-- Page specific script -->

  <!-- <script type="text/javascript">
      $(document).ready(
       function(){
       var base_url='<?php echo base_url();?>'
       var events = base_url+'calendar/getattEvents';
       var date = new Date()
       var d    = date.getDate(),
           m    = date.getMonth(),
           y    = date.getFullYear()
          
      $('#attcalendar').fullCalendar({
        header    : {
        left  : 'prev,next today',
        center: 'title',
        right : 'month,agendaWeek,agendaDay'
        },
        buttonText: {
        today: 'Today',
        month: 'Month',
        week : 'Week',
        day  : 'Day'
        },
        events    : events
      })});
    
</script> -->

 <!-- Control Sidebar -->
 
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
       <div class="control-sidebar-bg"></div>
</div>
<script>
// Radialize the colors
Highcharts.setOptions({
    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
        return {
            radialGradient: {
                cx: 0.5,
                cy: 0.3,
                r: 0.7
            },
            stops: [
                [0, color],
                [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    })
});

// Build the chart
Highcharts.chart('clockin-container', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'Attendance Reporting Method'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                connectorColor: 'silver'
            }
        }
    },
    series: [{
        name: 'Share',
        data: [
            { name: 'Manual Form', y: 64 },
            { name: 'BioTime', y: 11 },
            { name: 'Mobile Phones', y: 5 },
            { name: 'None', y: 20 },
          
        ]
    }],
    credits:[{
      enabled:'false',
    }

    ],
    exporting: {
        buttons: {
            contextButton: {
                enabled: false
            }    
        }
    }
  
});

</script>

<script>

//Daily average working hours
Highcharts.chart('monthlyhours', {

chart: {
    type: 'gauge',
    plotBackgroundColor: null,
    plotBackgroundImage: null,
    plotBorderWidth: 0,
    plotShadow: false
},

title: {
    text: 'Average Monthly Working Hours'
},

pane: {
    startAngle: -150,
    endAngle: 150,
    background: [{
        backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
                [0, '#FFF'],
                [1, '#333']
            ]
        },
        borderWidth: 0,
        outerRadius: '109%'
    }, {
        backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
                [0, '#333'],
                [1, '#FFF']
            ]
        },
        borderWidth: 1,
        outerRadius: '107%'
    }, {
        // default background
    }, {
        backgroundColor: '#DDD',
        borderWidth: 0,
        outerRadius: '105%',
        innerRadius: '103%'
    }]
},

// the value axis
yAxis: {
    min: 0,
    max: 24,

    minorTickInterval: 'auto',
    minorTickWidth: 1,
    minorTickLength: 10,
    minorTickPosition: 'inside',
    minorTickColor: '#666',

    tickPixelInterval: 30,
    tickWidth: 2,
    tickPosition: 'inside',
    tickLength: 10,
    tickColor: '#666',
    labels: {
        step: 2,
        rotation: 'auto'
    },
    title: {
        text: 'Hours'
    },
    plotBands: [{
        from: 0,
        to: 3,
        color: '#DF5353' // green
    }, {
        from: 3,
        to: 7,
        color: '#DDDF0D' // yellow
    }, {
        from: 7,
        to: 24,
        color: '#55BF3B' // green
    }]
},

series: [{
    name: 'Speed',
    data: [8],
    tooltip: {
        valueSuffix: ' Hrs'
    }
}],
credits:[{
      enabled:'false',
    }

    ],
    exporting: {
        buttons: {
            contextButton: {
                enabled: false
            }    
        }
    }

},
// Add some life
function (chart) {
if (!chart.renderer.forExport) {
    setInterval(function () {
        var point = chart.series[0].points[0],
            newVal,
            inc = Math.round((Math.random() - 0.5) * 1.1);

        newVal = point.y + inc;
        if (newVal < 0 || newVal > 200) {
            newVal = point.y - inc;
        }

        point.update(newVal);

    }, 3000);
}
});
</script>
<!-- Calendars -->
 <script type="text/javascript">

 document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('attcalendar');

 var base_url='<?php echo base_url();?>';
 //console.log(base_url);

  var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [ 'dayGrid', 'timeGrid', 'list', 'bootstrap' ],
    timeZone: 'UTC',
    themeSystem: 'bootstrap',
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    weekNumbers: true,
    eventLimit: true, // allow "more" link when too many events
    events: base_url+'calendar/getattEvents'
  });


  calendar.render();
});


  document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('dutycalendar');
  var base_url='<?php echo base_url();?>';

  var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [ 'dayGrid', 'timeGrid', 'list', 'bootstrap' ],
    timeZone: 'UTC',
    themeSystem: 'bootstrap',
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    weekNumbers: true,
    eventLimit: true, // allow "more" link when too many events
    events: base_url+'calendar/getdutyEvents'
    
  }
  
  
  );
 
  calendar.render();

}

);


</script>


<script type="text/javascript">
        
        $(document).ready(function(){

           // $.notify("Hello","success");

            var isPassChanged="1";

            if(isPassChanged!=1){

                $('#changepass').modal('show');
            }

            var url=window.location.href;

            if(url=="<?php echo base_url()?>rosta/actuals" || url=="<?php echo base_url()?>/rosta/actuals#"|| url=="<?php echo base_url()?>rosta/actuals#"|| url=="<?php echo base_url()?>rosta/fetch_report"|| url=="<?php echo base_url()?>rosta/actualsreport"|| url=="<?php echo base_url()?>rosta/tabular"){

                $('body').addClass('sidebar-collapse');
                $('#sidebar').toggleClass('active');

            };


        } );


    </script>



<!-- ./wrapper -->
<?php
$uri = $_SERVER['REQUEST_URI'];
 $uri; // Outputs: URI
 
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
 
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 $linkquery=$url; // Outputs: Full URL
 // Outputs: Query String
?>

<!-- Modal -->
<div class="modal fade" id="switch" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Switch Facility</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form class="form form-horizontal" action="<?php echo base_url(); ?>departments/switchDepartment" method="post">
      <div class="row">
      <div class="col-md-6">
                <div class="form-group" >
                    <label>District</label>
                    
                    <select id="district" name="district" onChange="getFacs($(this).val());" class="form-control">
                    <option value="" >SELECT District</option>
                            <?php
                                // onchange="this.form.submit()" 
                            //."_".$department->department
                            $districts=Modules::run("districts/getDistricts");
                               foreach ($districts as $district){
                            ?>
                            <option value="<?php echo urlencode($district->district_id)."_".urlencode($district->district); ?>"><?php echo ucwords($district->district); ?></option>
                            <?php }   ?>

                    </select>
                </div>
            </div>
       
      <div class="col-md-6">
                <div class="form-group" >
                    <label>Facility</label>
                    <select id="facility" name="facility" onChange="getDeps($(this).val());" class="form-control">
                    <option value="">All</option>
    
                    </select>
                </div>
            </div>
    </div>
    <div class="row">
     
            <div class="col-md-6">
                <div class="form-group" >
                    <label>Department</label>
                    <select id="depart" name="department" onChange="getDivisions($(this).val());" class="form-control">
                    <option value="">All</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="direct" value="<?php echo $linkquery; ?>" >
           

            <div class="col-md-6" style="display:none;">
                <div class="form-group">
                    <label>Division</label>
                    <select id="division" class="form-control" onChange="getUnits($(this).val());" name="division">
                    <option value="">All</option>
                    </select>
              </div>
           </div>
  </div>

  <div class="row" style="display:none;">
  <div class="col-md-6">
                <!-- < needs fixing> -->
                <div class="form-group">
                    <label>Section</label>
                    <select id="section" class="form-control" onChange="getUnits($(this).val());" name="section">
                    <option value="">All</option>
                    </select>
              </div>
           </div>

          

            <div class="col-md-6">
                  <div class="form-group">
                          <label>Unit</label>
                    <select id="unit" name="unit" onchange="this.form.submit()" class="form-control form-control">
                    <option value="">All</option>
                         
                    </select>
                </div>
            </div>
  </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane" aria-hidden="true">Switch</i></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times">Close</i></button>
      </div>

        </form>

    </div>

  </div>
</div>



  <!-- change password modal at ones own wish -->
  <div class="modal" id="changepassword" data-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content" >
                        <form method="post" action="<?php echo base_url(); ?>auth/changePass">
                        <div class="modal-header bg-default text-center">
                            <h3>Change Password</h3>
                            <h4 style="color:blue;"><?php echo $userdata['names']; ?> </h4>
                            <?php echo $this->session->flashdata('msg'); ?>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label>Old Password</label>
                                <input type="password" class="form-control" name="oldpass">
                            </div>
                            <div class="form-group">
                                <label>New password</i></label>
                                <input type="password" class="form-control" name="newpass">
                            </div>



                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="Submit" class="btn btn-success">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>


              <!--change password--modal for first logins (as a MUST)-->

    <div class="modal" id="changepass" data-backdrop="true">
                <div class="modal-dialog">
                    <div class="modal-content" >
                        <form method="post" action="<?php echo base_url(); ?>auth/changePass">
                        <div class="modal-header bg-default text-center">
                            <h2>Change  Password</h2>
                            <?php echo $this->session->flashdata('msg'); ?>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label>Old password</label>
                                <input type="password" class="form-control" name="oldpass">
                            </div>
                            <div class="form-group">
                                <label>New password></label>
                                <input type="password" class="form-control" name="newpass">
                            </div>



                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="Submit" class="btn btn-success">
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /change password--modal for first logins (as a MUST)-->

 



</body>
</html>


